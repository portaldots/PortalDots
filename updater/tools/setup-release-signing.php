#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use PortalDots\Updater\CanonicalJson;

$options = getopt('', ['repo:', 'key-dir:', 'generate', 'apply']);
$repo = $options['repo'] ?? null;
$keyDir = $options['key-dir'] ?? null;
$generate = array_key_exists('generate', $options);
$apply = array_key_exists('apply', $options);
if (!is_string($repo) || !preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo)
    || !is_string($keyDir) || $keyDir === '') {
    fwrite(STDERR, "Usage: setup-release-signing.php --repo OWNER/REPO --key-dir PATH [--generate] [--apply]\n");
    exit(64);
}
assertRepositoryMatches($repo);
$keyDir = absolutePath($keyDir);
assertKeyDirectoryOutsideRepository($keyDir);
if ($generate) {
    generateKeys($keyDir);
}

$repository = ghJson(['api', "repos/{$repo}"]);
$defaultBranch = $repository['default_branch'] ?? null;
if (!is_string($defaultBranch) || $defaultBranch === '') {
    throw new RuntimeException('Cannot determine the repository default branch.');
}
$state = protectionState($repo, $defaultBranch);
if (!$apply) {
    fwrite(STDOUT, CanonicalJson::encode([
        'mode' => 'dry-run',
        'repository' => $repo,
        'default_branch' => $defaultBranch,
        'release_tag_ruleset' => $state['tag_ruleset'],
        'release_signing_environment' => $state['release_signing'],
        'release_renewal_environment' => $state['release_renewal'],
        'keys_present' => keysExist($keyDir),
    ]) . "\n");
    if (!$state['tag_ruleset'] || !$state['release_signing'] || !$state['release_renewal']) {
        fwrite(STDERR, "GitHub release protections are incomplete. Re-run with --apply after reviewing this plan.\n");
        exit(2);
    }
    exit(0);
}
if (!keysExist($keyDir)) {
    throw new RuntimeException('Signing keys are missing. Use --generate first or together with --apply.');
}
validateKeys($keyDir);
assertNoExistingSecrets($repo);
assertVariablesSafeToSet($repo, $keyDir);
applyProtections($repo, $defaultBranch, $state);
$verifiedState = protectionState($repo, $defaultBranch);
if (!$verifiedState['tag_ruleset'] || !$verifiedState['release_signing']
    || !$verifiedState['release_renewal']) {
    throw new RuntimeException('GitHub protections could not be verified; no secrets were uploaded.');
}
setVariable($repo, 'UPDATER_PUBLIC_KEY', trim(readRequired($keyDir . '/release.pub')));
setVariable($repo, 'UPDATER_RENEWAL_PUBLIC_KEY', trim(readRequired($keyDir . '/renewal.pub')));
setSecret($repo, 'release-signing', 'UPDATER_SIGNING_KEY', readRequired($keyDir . '/release.key'));
setSecret($repo, 'release-signing', 'UPDATER_RENEWAL_SIGNING_KEY', readRequired($keyDir . '/renewal.key'));
setSecret($repo, 'release-renewal', 'UPDATER_RENEWAL_SIGNING_KEY', readRequired($keyDir . '/renewal.key'));
fwrite(STDOUT, "GitHub release signing setup completed without exposing private keys.\n");

function assertRepositoryMatches(string $repo): void
{
    [$status, $remote] = runProcess(['git', 'remote', 'get-url', 'origin']);
    if ($status !== 0) {
        throw new RuntimeException('Cannot read the origin remote.');
    }
    $remote = trim($remote);
    $patterns = [
        '#^https://github\.com/([A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+?)(?:\.git)?/?$#i',
        '#^git@github\.com:([A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+?)(?:\.git)?$#i',
        '#^ssh://git@github\.com/([A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+?)(?:\.git)?/?$#i',
    ];
    $remoteRepo = null;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $remote, $matches)) {
            $remoteRepo = $matches[1];
            break;
        }
    }
    if ($remoteRepo === null || strcasecmp($remoteRepo, $repo) !== 0) {
        throw new RuntimeException('--repo does not match the current checkout origin.');
    }
}

function absolutePath(string $path): string
{
    if (!str_starts_with($path, '/')) {
        $path = getcwd() . '/' . $path;
    }
    $parent = realpath(dirname($path));
    if ($parent === false) {
        throw new RuntimeException('The signing key parent directory must already exist.');
    }
    return $parent . '/' . basename($path);
}

function assertKeyDirectoryOutsideRepository(string $keyDir): void
{
    [$status, $root] = runProcess(['git', 'rev-parse', '--show-toplevel']);
    if ($status !== 0) {
        throw new RuntimeException('Cannot determine the repository path.');
    }
    $root = rtrim(trim($root), '/') . '/';
    $resolved = file_exists($keyDir) ? realpath($keyDir) : $keyDir;
    if ($resolved === false || is_link($keyDir) || str_starts_with(rtrim($resolved, '/') . '/', $root)) {
        throw new RuntimeException('Signing keys must be stored outside the Git checkout.');
    }
}

function generateKeys(string $keyDir): void
{
    if (file_exists($keyDir)) {
        throw new RuntimeException('The signing key directory already exists; refusing to overwrite it.');
    }
    if (!mkdir($keyDir, 0700) || !chmod($keyDir, 0700)) {
        throw new RuntimeException('Cannot create the signing key directory.');
    }
    foreach (['release', 'renewal'] as $name) {
        $keypair = sodium_crypto_sign_keypair();
        $secret = base64_encode(sodium_crypto_sign_secretkey($keypair)) . "\n";
        $public = base64_encode(sodium_crypto_sign_publickey($keypair)) . "\n";
        writeExclusive($keyDir . "/{$name}.key", $secret, 0600);
        writeExclusive($keyDir . "/{$name}.pub", $public, 0644);
    }
}

function writeExclusive(string $path, string $contents, int $mode): void
{
    $handle = fopen($path, 'xb');
    if ($handle === false || fwrite($handle, $contents) !== strlen($contents)
        || !fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
        if (is_resource($handle)) {
            fclose($handle);
        }
        @unlink($path);
        throw new RuntimeException('Cannot write signing key material.');
    }
    fclose($handle);
    chmod($path, $mode);
}

function keysExist(string $keyDir): bool
{
    foreach (['release.key', 'release.pub', 'renewal.key', 'renewal.pub'] as $file) {
        if (!is_file($keyDir . '/' . $file)) {
            return false;
        }
    }
    return true;
}

function validateKeys(string $keyDir): void
{
    $directoryMode = fileperms($keyDir);
    if (!is_dir($keyDir) || is_link($keyDir) || $directoryMode === false || ($directoryMode & 0077) !== 0) {
        throw new RuntimeException('Signing key directory permissions must be 0700 or stricter.');
    }
    foreach (['release', 'renewal'] as $name) {
        $secretPath = $keyDir . "/{$name}.key";
        $publicPath = $keyDir . "/{$name}.pub";
        $secretMode = fileperms($secretPath);
        if (!is_file($secretPath) || is_link($secretPath) || $secretMode === false || ($secretMode & 0077) !== 0
            || !is_file($publicPath) || is_link($publicPath)) {
            throw new RuntimeException("{$name} signing key files or permissions are unsafe.");
        }
        $secret = base64_decode(trim(readRequired($secretPath)), true);
        $public = base64_decode(trim(readRequired($publicPath)), true);
        if (!is_string($secret) || strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES
            || !is_string($public) || strlen($public) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            || !hash_equals($public, sodium_crypto_sign_publickey_from_secretkey($secret))) {
            throw new RuntimeException("{$name} signing key pair is invalid.");
        }
    }
}

/** @return array{tag_ruleset: bool, release_signing: bool, release_renewal: bool} */
function protectionState(string $repo, string $defaultBranch): array
{
    $rulesets = ghPagedList("repos/{$repo}/rulesets?per_page=100");
    $tagProtected = false;
    foreach ($rulesets as $summary) {
        if (!is_array($summary) || ($summary['name'] ?? null) !== 'PortalDots release tags'
            || !is_int($summary['id'] ?? null)) {
            continue;
        }
        $details = ghJson(['api', "repos/{$repo}/rulesets/{$summary['id']}"]);
        $types = array_map(static fn (array $rule): string => (string) ($rule['type'] ?? ''),
            is_array($details['rules'] ?? null) ? $details['rules'] : []);
        $includes = $details['conditions']['ref_name']['include'] ?? [];
        $excludes = $details['conditions']['ref_name']['exclude'] ?? [];
        $bypass = $details['bypass_actors'] ?? [];
        $allowedBypass = $bypass === [] || (is_array($bypass) && count($bypass) === 1
            && is_array($bypass[0] ?? null)
            && ($bypass[0]['actor_id'] ?? null) === 5
            && ($bypass[0]['actor_type'] ?? null) === 'RepositoryRole'
            && ($bypass[0]['bypass_mode'] ?? null) === 'always');
        $tagProtected = ($details['target'] ?? null) === 'tag'
            && ($details['enforcement'] ?? null) === 'active'
            && $includes === ['refs/tags/v*'] && $excludes === [] && $allowedBypass
            && count(array_intersect(['creation', 'update', 'deletion', 'non_fast_forward'], $types)) === 4;
        if (!$tagProtected) {
            throw new RuntimeException('Existing PortalDots release tag ruleset is not the required restrictive policy.');
        }
    }
    $environments = ghPagedList("repos/{$repo}/environments?per_page=100", 'environments');
    $names = [];
    foreach ($environments as $environment) {
        if (is_array($environment) && is_string($environment['name'] ?? null)) {
            $names[] = $environment['name'];
        }
    }
    return [
        'tag_ruleset' => $tagProtected,
        'release_signing' => in_array('release-signing', $names, true)
            && hasDeploymentPolicy($repo, 'release-signing', 'v*', 'tag'),
        'release_renewal' => in_array('release-renewal', $names, true)
            && hasDeploymentPolicy($repo, 'release-renewal', $defaultBranch, 'branch'),
    ];
}

function hasDeploymentPolicy(string $repo, string $environment, string $name, string $type): bool
{
    $details = ghJson(['api', "repos/{$repo}/environments/{$environment}"]);
    $deployment = $details['deployment_branch_policy'] ?? null;
    if (!is_array($deployment) || ($deployment['protected_branches'] ?? null) !== false
        || ($deployment['custom_branch_policies'] ?? null) !== true) {
        throw new RuntimeException("Environment {$environment} does not have an exclusive custom deployment policy.");
    }
    $policies = ghPagedList(
        "repos/{$repo}/environments/{$environment}/deployment-branch-policies?per_page=100",
        'branch_policies',
    );
    return count($policies) === 1 && is_array($policies[0])
        && ($policies[0]['name'] ?? null) === $name && ($policies[0]['type'] ?? null) === $type;
}

/** @param array{tag_ruleset: bool, release_signing: bool, release_renewal: bool} $state */
function applyProtections(string $repo, string $defaultBranch, array $state): void
{
    if (!$state['tag_ruleset']) {
        ghJson(['api', '--method', 'POST', "repos/{$repo}/rulesets", '--input', '-'], [
            'name' => 'PortalDots release tags',
            'target' => 'tag',
            'enforcement' => 'active',
            'bypass_actors' => [[
                'actor_id' => 5,
                'actor_type' => 'RepositoryRole',
                'bypass_mode' => 'always',
            ]],
            'conditions' => ['ref_name' => ['include' => ['refs/tags/v*'], 'exclude' => []]],
            'rules' => array_map(static fn (string $type): array => ['type' => $type],
                ['creation', 'update', 'deletion', 'non_fast_forward']),
        ]);
    }
    if (!$state['release_signing']) {
        createEnvironment($repo, 'release-signing', 'v*', 'tag');
    }
    if (!$state['release_renewal']) {
        createEnvironment($repo, 'release-renewal', $defaultBranch, 'branch');
    }
}

function createEnvironment(string $repo, string $environment, string $policy, string $type): void
{
    $existing = ghPagedList("repos/{$repo}/environments?per_page=100", 'environments');
    foreach ($existing as $entry) {
        if (is_array($entry) && ($entry['name'] ?? null) === $environment) {
            throw new RuntimeException("Existing environment {$environment} lacks the required policy; refusing to alter it.");
        }
    }
    ghJson(['api', '--method', 'PUT', "repos/{$repo}/environments/{$environment}", '--input', '-'], [
        'deployment_branch_policy' => ['protected_branches' => false, 'custom_branch_policies' => true],
    ]);
    ghJson(['api', '--method', 'POST',
        "repos/{$repo}/environments/{$environment}/deployment-branch-policies", '--input', '-'], [
        'name' => $policy,
        'type' => $type,
    ]);
}

function assertNoExistingSecrets(string $repo): void
{
    $existing = ghPagedList("repos/{$repo}/environments?per_page=100", 'environments');
    $names = [];
    foreach ($existing as $entry) {
        if (is_array($entry) && is_string($entry['name'] ?? null)) {
            $names[] = $entry['name'];
        }
    }
    foreach (['release-signing', 'release-renewal'] as $environment) {
        if (!in_array($environment, $names, true)) {
            continue;
        }
        [$status, $output] = runProcess(['gh', 'secret', 'list', '--repo', $repo, '--env', $environment,
            '--json', 'name']);
        if ($status !== 0) {
            throw new RuntimeException("Cannot inspect {$environment} secrets.");
        }
        $secrets = json_decode($output, true, 16, JSON_THROW_ON_ERROR);
        if (is_array($secrets) && $secrets !== []) {
            throw new RuntimeException("Existing {$environment} secrets will not be overwritten.");
        }
    }
}

function assertVariablesSafeToSet(string $repo, string $keyDir): void
{
    [$status, $output] = runProcess(['gh', 'variable', 'list', '--repo', $repo, '--json', 'name,value']);
    if ($status !== 0) {
        throw new RuntimeException('Cannot inspect repository variables.');
    }
    $expected = [
        'UPDATER_PUBLIC_KEY' => trim(readRequired($keyDir . '/release.pub')),
        'UPDATER_RENEWAL_PUBLIC_KEY' => trim(readRequired($keyDir . '/renewal.pub')),
    ];
    foreach (json_decode($output, true, 16, JSON_THROW_ON_ERROR) as $variable) {
        $name = is_array($variable) ? ($variable['name'] ?? null) : null;
        if (isset($expected[$name]) && !hash_equals($expected[$name], (string) ($variable['value'] ?? ''))) {
            throw new RuntimeException("Existing variable {$name} has a different value.");
        }
    }
}

function setVariable(string $repo, string $name, string $value): void
{
    [$status] = runProcess(['gh', 'variable', 'set', $name, '--repo', $repo, '--body', $value]);
    if ($status !== 0) {
        throw new RuntimeException("Cannot set repository variable {$name}.");
    }
}

function setSecret(string $repo, string $environment, string $name, string $value): void
{
    [$status] = runProcess(['gh', 'secret', 'set', $name, '--repo', $repo, '--env', $environment], $value);
    if ($status !== 0) {
        throw new RuntimeException("Cannot set {$environment} secret {$name}.");
    }
}

/** @param list<string> $arguments @param array<string, mixed>|null $input @return array<string, mixed> */
function ghJson(array $arguments, ?array $input = null): array
{
    [$status, $stdout, $stderr] = runProcess(array_merge(['gh'], $arguments),
        $input === null ? null : CanonicalJson::encode($input));
    if ($status !== 0) {
        throw new RuntimeException('GitHub CLI request failed: ' . trim($stderr));
    }
    $decoded = json_decode($stdout === '' ? '{}' : $stdout, true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        throw new RuntimeException('GitHub CLI returned malformed JSON.');
    }
    return $decoded;
}

/** @return list<mixed> */
function ghPagedList(string $endpoint, ?string $container = null): array
{
    $pages = ghJson(['api', '--paginate', '--slurp', $endpoint]);
    if (!array_is_list($pages)) {
        throw new RuntimeException('GitHub CLI pagination response is malformed.');
    }
    $result = [];
    foreach ($pages as $page) {
        $items = $container === null ? $page : (is_array($page) ? ($page[$container] ?? null) : null);
        if (!is_array($items) || !array_is_list($items)) {
            throw new RuntimeException('GitHub CLI pagination page is malformed.');
        }
        array_push($result, ...$items);
    }
    return $result;
}

/** @param list<string> $command @return array{int, string, string} */
function runProcess(array $command, ?string $stdin = null): array
{
    $process = proc_open($command, [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start a required command.');
    }
    if ($stdin !== null) {
        fwrite($pipes[0], $stdin);
    }
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), (string) $stdout, (string) $stderr];
}

function readRequired(string $path): string
{
    $contents = file_get_contents($path);
    if (!is_string($contents) || trim($contents) === '') {
        throw new RuntimeException('Signing key material is missing.');
    }
    return trim($contents);
}
