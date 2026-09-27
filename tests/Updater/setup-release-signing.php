#!/usr/bin/env php
<?php

declare(strict_types=1);

$repository = dirname(__DIR__, 2);
$root = sys_get_temp_dir() . '/portaldots-signing-setup-' . bin2hex(random_bytes(8));
$bin = $root . '/bin';
$statePath = $root . '/state.json';
$logPath = $root . '/gh.log';
$keyDir = $root . '/keys';
mkdir($bin, 0700, true);
try {
    $node = findExecutable('node');
    if ($node === null || !symlink($node, $bin . '/node')) {
        throw new RuntimeException('Cannot prepare isolated Node.js PATH.');
    }
    $dirname = findExecutable('dirname');
    if ($dirname === null || !symlink($dirname, $bin . '/dirname')) {
        throw new RuntimeException('Cannot prepare isolated shell PATH.');
    }
    file_put_contents($bin . '/gh', fakeGhSource());
    file_put_contents($bin . '/git', fakeGitSource());
    chmod($bin . '/gh', 0700);
    chmod($bin . '/git', 0700);
    writeState($statePath, state(false));
    $environment = [
        'PATH' => $bin,
        'HOME' => $root,
        'FAKE_GH_STATE' => $statePath,
        'FAKE_GH_LOG' => $logPath,
        'FAKE_GIT_ROOT' => $repository,
    ];
    $phpProbe = run(['/bin/sh', '-c', 'command -v php'], $root, $environment);
    if ($phpProbe['status'] === 0) {
        throw new RuntimeException('Isolated setup PATH unexpectedly contains PHP.');
    }
    $withoutHome = $environment;
    unset($withoutHome['HOME']);
    $homeRequired = run([
        '/bin/sh',
        $repository . '/updater/tools/setup-release-signing.sh',
        '--repo',
        'portaldots/PortalDots',
    ], $root, $withoutHome);
    if ($homeRequired['status'] !== 64 || readLog($logPath) !== []) {
        throw new RuntimeException('Missing HOME without --key-dir was not rejected before GitHub access.');
    }

    $wrongRemote = runSetup($repository, $keyDir, $environment + [
        'FAKE_GIT_REMOTE' => 'https://evil.example/github.com/portaldots/PortalDots.git',
    ]);
    if ($wrongRemote['status'] === 0 || readLog($logPath) !== []) {
        throw new RuntimeException('A non-GitHub origin was accepted.');
    }

    $dryRun = runSetup($repository, $keyDir, $environment);
    if ($dryRun['status'] !== 2 || file_exists($keyDir) || hasMutation(readLog($logPath))) {
        throw new RuntimeException('Dry run changed GitHub or generated keys.');
    }
    file_put_contents($logPath, '');
    $generated = runSetup($repository, $keyDir, $environment, ['--generate']);
    if ($generated['status'] !== 2 || (fileperms($keyDir) & 0777) !== 0700
        || (fileperms($keyDir . '/release.key') & 0777) !== 0600) {
        throw new RuntimeException('Node.js key generation did not preserve private permissions.');
    }
    assertLibsodiumCompatible($keyDir);

    chmod($keyDir . '/release.key', 0644);
    assertRejectedWithoutMutation($repository, $keyDir, $environment, $logPath,
        'Publicly readable private key was accepted.');
    chmod($keyDir . '/release.key', 0600);
    $renewalPublic = (string) file_get_contents($keyDir . '/renewal.pub');
    file_put_contents($keyDir . '/renewal.pub', (string) file_get_contents($keyDir . '/release.pub'));
    assertRejectedWithoutMutation($repository, $keyDir, $environment, $logPath,
        'Mismatched signing key pair was accepted.');
    file_put_contents($keyDir . '/renewal.pub', $renewalPublic);

    foreach ([['wide_policy' => true], ['bad_bypass' => true], ['excluded' => true]] as $unsafe) {
        writeState($statePath, array_merge(state(true), $unsafe));
        file_put_contents($logPath, '');
        $result = runSetup($repository, $keyDir, $environment, ['--apply']);
        if ($result['status'] === 0 || hasSecretWrite(readLog($logPath))) {
            throw new RuntimeException('Unsafe GitHub protection received signing secrets.');
        }
    }

    writeState($statePath, array_merge(state(true), ['existing_secret' => true]));
    assertRejectedWithoutMutation($repository, $keyDir, $environment, $logPath,
        'Existing secrets were overwritten.');

    writeState($statePath, state(false));
    file_put_contents($logPath, '');
    $applied = runSetup($repository, $keyDir, $environment, ['--apply']);
    if ($applied['status'] !== 0) {
        throw new RuntimeException('Protected setup failed: ' . $applied['stderr']);
    }
    assertSecretsStayedOnStdin($keyDir, readLog($logPath), $applied);

    writeState($statePath, state(false));
    file_put_contents($logPath, '');
    $relative = 'relative keys with spaces';
    $relativeAbsolute = $root . '/' . $relative;
    $oneCommand = runSetup($repository, $relative, $environment, ['--apply'], $root);
    if ($oneCommand['status'] !== 0 || !is_file($relativeAbsolute . '/release.key')) {
        throw new RuntimeException('One-command setup did not preserve caller-relative key path: '
            . $oneCommand['stderr']);
    }
    assertLibsodiumCompatible($relativeAbsolute);

    $phpKeyDir = $root . '/php-generated-keys';
    writeLibsodiumKeys($phpKeyDir);
    writeState($statePath, state(false));
    file_put_contents($logPath, '');
    $phpGenerated = runSetup($repository, $phpKeyDir, $environment, ['--apply']);
    if ($phpGenerated['status'] !== 0) {
        throw new RuntimeException('Node.js setup rejected existing libsodium keys: ' . $phpGenerated['stderr']);
    }
    assertSecretsStayedOnStdin($phpKeyDir, readLog($logPath), $phpGenerated);

    writeState($statePath, array_merge(state(true), ['existing_secret' => true, 'secret_failure_echo' => true]));
    $savedKey = (string) file_get_contents($relativeAbsolute . '/release.key');
    $repeat = runSetup($repository, $relative, $environment, ['--apply'], $root);
    if ($repeat['status'] === 0 || file_get_contents($relativeAbsolute . '/release.key') !== $savedKey
        || str_contains($repeat['stdout'] . $repeat['stderr'], trim($savedKey))) {
        throw new RuntimeException('Repeated setup replaced or exposed an existing key.');
    }

    writeState($statePath, state(false));
    file_put_contents($logPath, '');
    $failureDir = $root . '/failure-keys';
    $failureEnvironment = $environment + ['FAKE_GH_SECRET_FAILURE' => '1'];
    $failure = runSetup($repository, $failureDir, $failureEnvironment, ['--apply']);
    $failureSecret = is_file($failureDir . '/release.key')
        ? trim((string) file_get_contents($failureDir . '/release.key')) : '';
    if ($failure['status'] === 0 || $failureSecret === ''
        || str_contains($failure['stdout'] . $failure['stderr'], $failureSecret)) {
        throw new RuntimeException('Secret command failure exposed its stdin.');
    }

    fwrite(STDOUT, "Node setup works without PHP and keeps libsodium keys and secrets compatible\n");
} finally {
    removeTree($root);
}

/** @return array<string, bool> */
function state(bool $protected): array
{
    return [
        'protected' => $protected,
        'existing_secret' => false,
        'wide_policy' => false,
        'bad_bypass' => false,
        'excluded' => false,
    ];
}

function assertRejectedWithoutMutation(
    string $repository,
    string $keyDir,
    array $environment,
    string $logPath,
    string $message
): void {
    file_put_contents($logPath, '');
    $result = runSetup($repository, $keyDir, $environment, ['--apply']);
    if ($result['status'] === 0 || hasMutation(readLog($logPath))) {
        throw new RuntimeException($message);
    }
}

function assertLibsodiumCompatible(string $keyDir): void
{
    foreach (['release', 'renewal'] as $name) {
        $secret = base64_decode(trim((string) file_get_contents($keyDir . "/{$name}.key")), true);
        $public = base64_decode(trim((string) file_get_contents($keyDir . "/{$name}.pub")), true);
        if (!is_string($secret) || strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES
            || !is_string($public) || strlen($public) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            || !hash_equals($public, sodium_crypto_sign_publickey_from_secretkey($secret))) {
            throw new RuntimeException('Node.js key is not compatible with libsodium Ed25519 format.');
        }
        $message = 'PortalDots setup compatibility';
        $signature = sodium_crypto_sign_detached($message, $secret);
        if (!sodium_crypto_sign_verify_detached($signature, $message, $public)) {
            throw new RuntimeException('Node.js key cannot sign with libsodium.');
        }
    }
}

function writeLibsodiumKeys(string $keyDir): void
{
    mkdir($keyDir, 0700);
    chmod($keyDir, 0700);
    foreach (['release', 'renewal'] as $name) {
        $pair = sodium_crypto_sign_keypair();
        file_put_contents($keyDir . "/{$name}.key", base64_encode(sodium_crypto_sign_secretkey($pair)) . "\n");
        file_put_contents($keyDir . "/{$name}.pub", base64_encode(sodium_crypto_sign_publickey($pair)) . "\n");
        chmod($keyDir . "/{$name}.key", 0600);
        chmod($keyDir . "/{$name}.pub", 0644);
    }
}

/** @param list<array<string, mixed>> $log @param array{stdout: string, stderr: string} $result */
function assertSecretsStayedOnStdin(string $keyDir, array $log, array $result): void
{
    $release = trim((string) file_get_contents($keyDir . '/release.key'));
    $renewal = trim((string) file_get_contents($keyDir . '/renewal.key'));
    $serialized = json_encode($log, JSON_THROW_ON_ERROR) . $result['stdout'] . $result['stderr'];
    if (str_contains($serialized, $release) || str_contains($serialized, $renewal)) {
        throw new RuntimeException('Private key appeared in arguments or output.');
    }
    $writes = array_values(array_filter($log, static fn (array $entry): bool =>
        ($entry['args'][0] ?? null) === 'secret' && ($entry['args'][1] ?? null) === 'set'));
    if (count($writes) !== 3
        || countSecretInput($writes, 'UPDATER_SIGNING_KEY', hash('sha256', $release)) !== 1
        || countSecretInput($writes, 'UPDATER_RENEWAL_SIGNING_KEY', hash('sha256', $renewal)) !== 2) {
        throw new RuntimeException('Private keys were not sent only through expected secret stdin streams.');
    }
}

/** @param list<array<string, mixed>> $writes */
function countSecretInput(array $writes, string $name, string $hash): int
{
    return count(array_filter($writes, static fn (array $entry): bool =>
        ($entry['args'][2] ?? null) === $name && hash_equals($hash, (string) $entry['stdin_sha256'])));
}

/** @param list<string> $extra @return array{status: int, stdout: string, stderr: string} */
function runSetup(
    string $repository,
    string $keyDir,
    array $environment,
    array $extra = [],
    ?string $cwd = null
): array {
    return run(array_merge(['/bin/sh', $repository . '/updater/tools/setup-release-signing.sh',
        '--repo', 'portaldots/PortalDots', '--key-dir', $keyDir], $extra), $cwd ?? $repository, $environment);
}

/** @param list<string> $command @return array{status: int, stdout: string, stderr: string} */
function run(array $command, string $cwd, array $environment): array
{
    $process = proc_open($command, [
        0 => ['file', '/dev/null', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes, $cwd, $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start setup fixture process.');
    }
    $stdout = (string) stream_get_contents($pipes[1]);
    $stderr = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return ['status' => proc_close($process), 'stdout' => $stdout, 'stderr' => $stderr];
}

/** @param array<string, mixed> $value */
function writeState(string $path, array $value): void
{
    file_put_contents($path, json_encode($value, JSON_THROW_ON_ERROR));
}

/** @return list<array<string, mixed>> */
function readLog(string $path): array
{
    $result = [];
    if (!is_file($path)) {
        return $result;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $result[] = json_decode($line, true, 32, JSON_THROW_ON_ERROR);
    }
    return $result;
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

function findExecutable(string $name): ?string
{
    foreach (explode(PATH_SEPARATOR, (string) getenv('PATH')) as $directory) {
        $path = $directory . DIRECTORY_SEPARATOR . $name;
        if (is_file($path) && is_executable($path)) {
            return realpath($path) ?: $path;
        }
    }
    return null;
}

function fakeGhSource(): string
{
    return <<<'JS'
#!/usr/bin/env node
import { createHash } from 'node:crypto';
import { appendFileSync, readFileSync, writeFileSync } from 'node:fs';

const statePath = process.env.FAKE_GH_STATE;
const logPath = process.env.FAKE_GH_LOG;
const state = JSON.parse(readFileSync(statePath, 'utf8'));
const args = process.argv.slice(2);
let stdin = '';
for await (const chunk of process.stdin) stdin += chunk;
appendFileSync(logPath, `${JSON.stringify({
  args,
  stdin_sha256: createHash('sha256').update(stdin).digest('hex'),
  stdin_length: Buffer.byteLength(stdin),
})}\n`);
const output = (value) => { process.stdout.write(JSON.stringify(value)); process.exit(0); };

if (args[0] === 'api') {
  const endpoint = args.find((argument) => argument.startsWith('repos/')) ?? '';
  const methodIndex = args.indexOf('--method');
  const method = methodIndex === -1 ? 'GET' : args[methodIndex + 1];
  if (endpoint === 'repos/portaldots/PortalDots') output({ default_branch: '5.x' });
  if (endpoint.includes('/rulesets/') && !endpoint.endsWith('/rulesets')) {
    output({
      target: 'tag', enforcement: 'active',
      bypass_actors: state.bad_bypass
        ? [{ actor_id: 2, actor_type: 'RepositoryRole', bypass_mode: 'always' }]
        : [{ bypass_mode: 'always', actor_type: 'RepositoryRole', actor_id: 5 }],
      conditions: { ref_name: { include: ['refs/tags/v*'], exclude: state.excluded ? ['refs/tags/v6.*'] : [] } },
      rules: ['creation', 'update', 'deletion', 'non_fast_forward'].map((type) => ({ type })),
    });
  }
  if (endpoint.includes('/deployment-branch-policies')) {
    if (method === 'POST') output({ id: 1 });
    const signing = endpoint.includes('release-signing');
    const policies = [signing ? { name: 'v*', type: 'tag' } : { name: '5.x', type: 'branch' }];
    if (state.wide_policy) policies.push({ name: '*', type: 'branch' });
    output([{ branch_policies: policies }]);
  }
  if (/\/environments\/(release-signing|release-renewal)$/.test(endpoint)) {
    if (method === 'PUT') {
      state.environments ??= [];
      state.environments = [...new Set([...state.environments, endpoint.split('/').at(-1)])];
      writeFileSync(statePath, JSON.stringify(state));
      output({ name: endpoint.split('/').at(-1) });
    }
    output({ deployment_branch_policy: { protected_branches: false, custom_branch_policies: true } });
  }
  if (endpoint.includes('/environments')) {
    const names = state.protected ? ['release-signing', 'release-renewal'] : (state.environments ?? []);
    output([{ environments: names.map((name) => ({ name })) }]);
  }
  if (endpoint.includes('/rulesets')) {
    if (method === 'POST') {
      state.tag_protected = true;
      writeFileSync(statePath, JSON.stringify(state));
      output({ id: 1 });
    }
    output([(state.protected || state.tag_protected) ? [{ name: 'PortalDots release tags', id: 1 }] : []]);
  }
}
if (args[0] === 'secret' && args[1] === 'list') output(state.existing_secret ? [{ name: 'EXISTING' }] : []);
if (args[0] === 'variable' && args[1] === 'list') output([]);
if (args[0] === 'secret' && args[1] === 'set' && process.env.FAKE_GH_SECRET_FAILURE === '1') {
  process.stderr.write(stdin);
  process.exit(1);
}
process.exit(0);
JS;
}

function fakeGitSource(): string
{
    return <<<'JS'
#!/usr/bin/env node
const args = process.argv.slice(2);
if (JSON.stringify(args) === JSON.stringify(['remote', 'get-url', 'origin'])) {
  process.stdout.write(`${process.env.FAKE_GIT_REMOTE ?? 'git@github.com:portaldots/PortalDots.git'}\n`);
  process.exit(0);
}
if (JSON.stringify(args) === JSON.stringify(['rev-parse', '--show-toplevel'])) {
  process.stdout.write(`${process.env.FAKE_GIT_ROOT}\n`);
  process.exit(0);
}
process.exit(1);
JS;
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
