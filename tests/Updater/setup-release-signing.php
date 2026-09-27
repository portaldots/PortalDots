#!/usr/bin/env php
<?php

declare(strict_types=1);

$repository = dirname(__DIR__, 2);
$root = sys_get_temp_dir() . '/portaldots-signing-setup-' . bin2hex(random_bytes(8));
mkdir($root . '/bin', 0700, true);
$statePath = $root . '/state.json';
$logPath = $root . '/gh.log';
$keyDir = $root . '/keys';
try {
    file_put_contents($root . '/bin/gh', fakeGhSource());
    chmod($root . '/bin/gh', 0700);
    file_put_contents($root . '/bin/git', fakeGitSource());
    chmod($root . '/bin/git', 0700);
    writeState($statePath, ['protected' => false, 'existing_secret' => false, 'wide_policy' => false,
        'bad_bypass' => false, 'excluded' => false]);
    $environment = [
        'PATH' => $root . '/bin:' . (string) getenv('PATH'),
        'FAKE_GH_STATE' => $statePath,
        'FAKE_GH_LOG' => $logPath,
        'FAKE_GIT_ROOT' => $repository,
    ];

    $wrongRemoteEnvironment = $environment + [
        'FAKE_GIT_REMOTE' => 'https://evil.example/github.com/portaldots/PortalDots.git',
    ];
    $wrongRemote = runSetup($repository, $keyDir, $wrongRemoteEnvironment);
    if ($wrongRemote['status'] === 0 || (is_file($logPath) && readLog($logPath) !== [])) {
        throw new RuntimeException('A non-GitHub origin was accepted.');
    }

    $dryRun = runSetup($repository, $keyDir, $environment);
    if ($dryRun['status'] !== 2 || file_exists($keyDir) || hasMutation(readLog($logPath))) {
        throw new RuntimeException('Dry run changed GitHub or generated keys.');
    }
    file_put_contents($logPath, '');
    $generated = runSetup($repository, $keyDir, $environment, ['--generate']);
    if ($generated['status'] !== 2 || !is_dir($keyDir)
        || (fileperms($keyDir) & 0777) !== 0700
        || (fileperms($keyDir . '/release.key') & 0777) !== 0600) {
        throw new RuntimeException('Local key generation did not preserve private permissions.');
    }

    chmod($keyDir . '/release.key', 0644);
    file_put_contents($logPath, '');
    $publicKeyFile = runSetup($repository, $keyDir, $environment, ['--apply']);
    if ($publicKeyFile['status'] === 0 || hasMutation(readLog($logPath))) {
        throw new RuntimeException('Publicly readable private key was accepted.');
    }
    chmod($keyDir . '/release.key', 0600);
    $validPublic = (string) file_get_contents($keyDir . '/renewal.pub');
    file_put_contents($keyDir . '/renewal.pub', (string) file_get_contents($keyDir . '/release.pub'));
    file_put_contents($logPath, '');
    $mismatch = runSetup($repository, $keyDir, $environment, ['--apply']);
    if ($mismatch['status'] === 0 || hasMutation(readLog($logPath))) {
        throw new RuntimeException('Mismatched signing key pair was accepted.');
    }
    file_put_contents($keyDir . '/renewal.pub', $validPublic);

    foreach ([
        ['wide_policy' => true],
        ['bad_bypass' => true],
        ['excluded' => true],
    ] as $unsafe) {
        writeState($statePath, array_merge([
            'protected' => true,
            'existing_secret' => false,
            'wide_policy' => false,
            'bad_bypass' => false,
            'excluded' => false,
        ], $unsafe));
        file_put_contents($logPath, '');
        $result = runSetup($repository, $keyDir, $environment, ['--apply']);
        if ($result['status'] === 0 || hasSecretWrite(readLog($logPath))) {
            throw new RuntimeException('Unsafe GitHub protection received signing secrets.');
        }
    }

    writeState($statePath, ['protected' => true, 'existing_secret' => true, 'wide_policy' => false,
        'bad_bypass' => false, 'excluded' => false]);
    file_put_contents($logPath, '');
    $existing = runSetup($repository, $keyDir, $environment, ['--apply']);
    if ($existing['status'] === 0 || hasMutation(readLog($logPath))) {
        throw new RuntimeException('Existing secrets were overwritten.');
    }

    writeState($statePath, ['protected' => false, 'existing_secret' => false, 'wide_policy' => false,
        'bad_bypass' => false, 'excluded' => false]);
    file_put_contents($logPath, '');
    $applied = runSetup($repository, $keyDir, $environment, ['--apply']);
    if ($applied['status'] !== 0) {
        throw new RuntimeException('Protected setup failed: ' . $applied['stderr']);
    }
    $log = readLog($logPath);
    $releaseSecret = trim((string) file_get_contents($keyDir . '/release.key'));
    $renewalSecret = trim((string) file_get_contents($keyDir . '/renewal.key'));
    $serialized = json_encode($log, JSON_THROW_ON_ERROR) . $applied['stdout'] . $applied['stderr'];
    if (str_contains($serialized, $releaseSecret) || str_contains($serialized, $renewalSecret)) {
        throw new RuntimeException('Private key appeared in arguments or output.');
    }
    $secretWrites = array_values(array_filter($log, static fn (array $entry): bool =>
        ($entry['args'][0] ?? null) === 'secret' && ($entry['args'][1] ?? null) === 'set'));
    if (count($secretWrites) !== 3
        || !hasSecretInput($secretWrites, 'UPDATER_SIGNING_KEY', hash('sha256', $releaseSecret))
        || countSecretInput($secretWrites, 'UPDATER_RENEWAL_SIGNING_KEY', hash('sha256', $renewalSecret)) !== 2) {
        throw new RuntimeException('Private keys were not sent only through expected secret stdin streams.');
    }
    fwrite(STDOUT, "release signing setup remained fail-closed and kept secrets off argv/output\n");
} finally {
    removeTree($root);
}

/** @param list<string> $extra @return array{status: int, stdout: string, stderr: string} */
function runSetup(string $repository, string $keyDir, array $environment, array $extra = []): array
{
    $command = array_merge([PHP_BINARY, $repository . '/updater/tools/setup-release-signing.php',
        '--repo', 'portaldots/PortalDots', '--key-dir', $keyDir], $extra);
    $process = proc_open($command, [
        0 => ['file', '/dev/null', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes, $repository, array_merge($_ENV, $environment));
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start setup helper.');
    }
    $stdout = (string) stream_get_contents($pipes[1]);
    $stderr = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return ['status' => proc_close($process), 'stdout' => $stdout, 'stderr' => $stderr];
}

/** @param array<string, mixed> $state */
function writeState(string $path, array $state): void
{
    file_put_contents($path, json_encode($state, JSON_THROW_ON_ERROR));
}

/** @return list<array<string, mixed>> */
function readLog(string $path): array
{
    $entries = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $entries[] = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
    }
    return $entries;
}

/** @param list<array<string, mixed>> $log */
function hasMutation(array $log): bool
{
    foreach ($log as $entry) {
        $args = $entry['args'];
        if (($args[0] ?? null) === 'secret' && ($args[1] ?? null) === 'set'
            || ($args[0] ?? null) === 'variable' && ($args[1] ?? null) === 'set'
            || in_array('--method', $args, true)) {
            return true;
        }
    }
    return false;
}

/** @param list<array<string, mixed>> $log */
function hasSecretWrite(array $log): bool
{
    foreach ($log as $entry) {
        if (($entry['args'][0] ?? null) === 'secret' && ($entry['args'][1] ?? null) === 'set') {
            return true;
        }
    }
    return false;
}

/** @param list<array<string, mixed>> $writes */
function hasSecretInput(array $writes, string $name, string $hash): bool
{
    return countSecretInput($writes, $name, $hash) === 1;
}

/** @param list<array<string, mixed>> $writes */
function countSecretInput(array $writes, string $name, string $hash): int
{
    return count(array_filter($writes, static fn (array $entry): bool =>
        ($entry['args'][2] ?? null) === $name && hash_equals($hash, (string) $entry['stdin_sha256'])));
}

function fakeGhSource(): string
{
    return <<<'PHP'
#!/usr/bin/env php
<?php
$statePath = getenv('FAKE_GH_STATE');
$logPath = getenv('FAKE_GH_LOG');
$state = json_decode(file_get_contents($statePath), true, 16, JSON_THROW_ON_ERROR);
$args = array_slice($argv, 1);
$stdin = stream_get_contents(STDIN);
file_put_contents($logPath, json_encode([
    'args' => $args,
    'stdin_sha256' => hash('sha256', $stdin),
    'stdin_length' => strlen($stdin),
], JSON_THROW_ON_ERROR) . "\n", FILE_APPEND);
$json = static function (array $value): never {
    echo json_encode($value, JSON_THROW_ON_ERROR);
    exit(0);
};
if (($args[0] ?? null) === 'api') {
    $endpoint = '';
    foreach ($args as $arg) {
        if (str_starts_with($arg, 'repos/')) {
            $endpoint = $arg;
        }
    }
    $methodIndex = array_search('--method', $args, true);
    $method = $methodIndex === false ? 'GET' : ($args[$methodIndex + 1] ?? 'GET');
    if ($endpoint === 'repos/portaldots/PortalDots') {
        $json(['default_branch' => '5.x']);
    }
    if (str_contains($endpoint, '/rulesets/') && !str_ends_with($endpoint, '/rulesets')) {
        $bypass = $state['bad_bypass']
            ? [['actor_id' => 2, 'actor_type' => 'RepositoryRole', 'bypass_mode' => 'always']]
            : [['bypass_mode' => 'always', 'actor_type' => 'RepositoryRole', 'actor_id' => 5]];
        $json([
            'target' => 'tag', 'enforcement' => 'active', 'bypass_actors' => $bypass,
            'conditions' => ['ref_name' => [
                'include' => ['refs/tags/v*'],
                'exclude' => $state['excluded'] ? ['refs/tags/v6.*'] : [],
            ]],
            'rules' => array_map(fn ($type) => ['type' => $type],
                ['creation', 'update', 'deletion', 'non_fast_forward']),
        ]);
    }
    if (str_contains($endpoint, '/deployment-branch-policies')) {
        if ($method === 'POST') {
            file_put_contents($statePath, json_encode($state));
            $json(['id' => 1]);
        }
        $environment = str_contains($endpoint, 'release-signing') ? 'release-signing' : 'release-renewal';
        $policy = $environment === 'release-signing'
            ? ['name' => 'v*', 'type' => 'tag']
            : ['name' => '5.x', 'type' => 'branch'];
        $policies = [$policy];
        if ($state['wide_policy']) {
            $policies[] = ['name' => '*', 'type' => 'branch'];
        }
        $json([['branch_policies' => $policies]]);
    }
    if (preg_match('#/environments/(release-signing|release-renewal)$#', $endpoint)) {
        if ($method === 'PUT') {
            $state['environments'] ??= [];
            $state['environments'][] = basename($endpoint);
            $state['environments'] = array_values(array_unique($state['environments']));
            file_put_contents($statePath, json_encode($state));
            $json(['name' => basename($endpoint)]);
        }
        $json(['deployment_branch_policy' => [
            'protected_branches' => false,
            'custom_branch_policies' => true,
        ]]);
    }
    if (str_contains($endpoint, '/environments')) {
        $names = $state['protected']
            ? ['release-signing', 'release-renewal']
            : ($state['environments'] ?? []);
        $environments = array_map(fn ($name) => ['name' => $name], $names);
        $json([['environments' => $environments]]);
    }
    if (str_contains($endpoint, '/rulesets')) {
        if ($method === 'POST') {
            $state['tag_protected'] = true;
            file_put_contents($statePath, json_encode($state));
            $json(['id' => 1]);
        }
        $rulesets = ($state['protected'] || ($state['tag_protected'] ?? false))
            ? [['name' => 'PortalDots release tags', 'id' => 1]] : [];
        $json([ $rulesets ]);
    }
}
if (($args[0] ?? null) === 'secret' && ($args[1] ?? null) === 'list') {
    $json($state['existing_secret'] ? [['name' => 'EXISTING']] : []);
}
if (($args[0] ?? null) === 'variable' && ($args[1] ?? null) === 'list') {
    $json([]);
}
exit(0);
PHP;
}

function fakeGitSource(): string
{
    return <<<'PHP'
#!/usr/bin/env php
<?php
$args = array_slice($argv, 1);
if ($args === ['remote', 'get-url', 'origin']) {
    echo (getenv('FAKE_GIT_REMOTE') ?: 'git@github.com:portaldots/PortalDots.git'), "\n";
    exit(0);
}
if ($args === ['rev-parse', '--show-toplevel']) {
    echo getenv('FAKE_GIT_ROOT'), "\n";
    exit(0);
}
exit(1);
PHP;
}

function removeTree(string $path): void
{
    if (!is_dir($path)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) {
        $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}
