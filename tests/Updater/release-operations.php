#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\Config;
use PortalDots\Updater\ManifestVerifier;
use PortalDots\Updater\StateStore;
use PortalDots\Updater\TrustedKeyStore;

$root = sys_get_temp_dir() . '/portaldots-release-operations-' . bin2hex(random_bytes(8));
mkdir($root . '/updater/keys', 0700, true);
mkdir($root . '/private', 0700, true);
try {
    $rootKeys = sodium_crypto_sign_keypair();
    $rootPublic = sodium_crypto_sign_publickey($rootKeys);
    $rootSecret = sodium_crypto_sign_secretkey($rootKeys);
    $renewalKeys = sodium_crypto_sign_keypair();
    $renewalPublic = sodium_crypto_sign_publickey($renewalKeys);
    $renewalSecret = sodium_crypto_sign_secretkey($renewalKeys);
    file_put_contents($root . '/updater/keys/release.pub', base64_encode($rootPublic));
    file_put_contents($root . '/update.zip', 'update-archive');
    file_put_contents($root . '/full.zip', 'full-archive');
    $signed = [
        'schema' => 2,
        'sequence' => 10,
        'issued_at' => gmdate(DATE_ATOM, time() - 7200),
        'expires_at' => gmdate(DATE_ATOM, time() - 3600),
        'target_version' => '6.0.1',
        'from_versions' => ['6.0.0'],
        'minimum_php' => '8.3.0',
        'minimum_mysql' => '8.4.0',
        'minimum_runtime' => 2,
        'artifact' => artifact('update.zip', $root . '/update.zip'),
        'full_artifact' => artifact('PortalDots.zip', $root . '/full.zip'),
        'freshness' => [
            'keyid' => hash('sha256', $renewalPublic),
            'public_key' => base64_encode($renewalPublic),
            'sequence_floor' => 10,
            'max_lease_seconds' => 3_888_000,
        ],
        'files' => [['path' => 'app/test.php', 'sha256' => str_repeat('a', 64), 'size' => 1, 'mode' => 0644]],
        'migrations' => [],
        'next_keys' => [],
        'retired_keys' => [],
    ];
    file_put_contents($root . '/signed.json', CanonicalJson::encode($signed));
    $environment = [
        'UPDATER_SIGNING_KEY' => base64_encode($rootSecret),
        'UPDATER_PUBLIC_KEY' => base64_encode($rootPublic),
        'UPDATER_RENEWAL_SIGNING_KEY' => base64_encode($renewalSecret),
    ];
    mkdir($root . '/dist/updater/keys', 0700, true);
    mkdir($root . '/dist/app', 0700, true);
    file_put_contents($root . '/dist/updater/keys/release.pub', "UNCONFIGURED\n");
    file_put_contents($root . '/dist/app/test.php', "<?php\nreturn true;\n");
    file_put_contents($root . '/candidates.json', "[]\n");
    run([PHP_BINARY, dirname(__DIR__, 2) . '/updater/tools/build-release.php',
        $root . '/dist', $root . '/built', '6.0.0', '10', base64_encode($rootPublic),
        base64_encode($renewalPublic), $root . '/candidates.json'], $environment);
    $bootstrap = json_decode((string) file_get_contents($root . '/built/PortalDots-update-signed.json'),
        true, 128, JSON_THROW_ON_ERROR);
    if ($bootstrap['schema'] !== 2 || $bootstrap['from_versions'] !== []
        || !hash_equals($bootstrap['full_artifact']['sha256'],
            (string) hash_file('sha256', $root . '/built/PortalDots.zip'))) {
        throw new RuntimeException('Automatic bootstrap release did not bind its full artifact.');
    }
    run([PHP_BINARY, dirname(__DIR__, 2) . '/updater/tools/sign-manifest.php',
        $root . '/signed.json', $root . '/root.json'], $environment);
    run([PHP_BINARY, dirname(__DIR__, 2) . '/updater/tools/sign-lease.php',
        $root . '/root.json', $root . '/manifest-100.json', '100', $root . '/update.zip', $root . '/full.zip'],
        $environment);
    $first = json_decode((string) file_get_contents($root . '/manifest-100.json'), true, 128,
        JSON_THROW_ON_ERROR);
    run([PHP_BINARY, dirname(__DIR__, 2) . '/updater/tools/sign-lease.php',
        $root . '/manifest-100.json', $root . '/manifest-101.json', '101', $root . '/update.zip',
        $root . '/full.zip'], $environment);
    $second = json_decode((string) file_get_contents($root . '/manifest-101.json'), true, 128,
        JSON_THROW_ON_ERROR);
    if (!hash_equals(hash('sha256', CanonicalJson::encode([$first['signed'], $first['signatures']])),
        hash('sha256', CanonicalJson::encode([$second['signed'], $second['signatures']])))) {
        throw new RuntimeException('Lease renewal changed root-signed metadata.');
    }
    if ($second['lease']['signed']['sequence'] !== 101) {
        throw new RuntimeException('Lease renewal did not advance its sequence.');
    }
    $config = new Config($root, $root . '/private', 'https://github.com/manifest', ['github.com'],
        $root . '/updater/keys/release.pub');
    $verified = (new ManifestVerifier($config, new TrustedKeyStore($config, new StateStore($config))))
        ->verify($second, '6.0.0');
    if ($verified['lease_sequence'] !== 101) {
        throw new RuntimeException('Renewed manifest did not verify.');
    }
    file_put_contents($root . '/full.zip', 'changed');
    if (run([PHP_BINARY, dirname(__DIR__, 2) . '/updater/tools/sign-lease.php',
        $root . '/manifest-101.json', $root . '/bad.json', '102', $root . '/update.zip', $root . '/full.zip'],
        $environment, false) === 0) {
        throw new RuntimeException('Changed release artifact was renewed.');
    }

    $asset = [['name' => 'PortalDots-update-manifest.json',
        'browser_download_url' => 'https://github.com/portaldots/PortalDots/manifest']];
    $release = static fn (string $tag, bool $draft = false): array => [
        'tag_name' => $tag,
        'draft' => $draft,
        'prerelease' => false,
        'assets' => $asset,
    ];
    file_put_contents($root . '/releases.json', json_encode([
        [$release('v5.1.0'), $release('v5.2.0')],
        [$release('v6.0.0'), $release('v6.1.0', true)],
    ], JSON_THROW_ON_ERROR));
    run([PHP_BINARY, dirname(__DIR__, 2) . '/updater/tools/select-renewal-targets.php',
        $root . '/releases.json', $root . '/targets.txt'], $environment);
    if ((string) file_get_contents($root . '/targets.txt') !== "v5.2.0\nv6.0.0\n") {
        throw new RuntimeException('Renewal targets were not the latest published stable releases per major.');
    }
    file_put_contents($root . '/releases-bad.json', json_encode([[['bad']]], JSON_THROW_ON_ERROR));
    if (run([PHP_BINARY, dirname(__DIR__, 2) . '/updater/tools/select-renewal-targets.php',
        $root . '/releases-bad.json', $root . '/targets-bad.txt'], $environment, false) === 0) {
        throw new RuntimeException('Malformed paginated releases were accepted for renewal.');
    }
    fwrite(STDOUT, "lease renewal preserved root metadata and artifacts\n");
} finally {
    removeTree($root);
}

/** @return array{url: string, size: int, sha256: string} */
function artifact(string $name, string $path): array
{
    return [
        'url' => "https://github.com/portaldots/PortalDots/releases/download/v6.0.1/{$name}",
        'size' => filesize($path),
        'sha256' => hash_file('sha256', $path),
    ];
}

/** @param list<string> $command @param array<string, string> $environment */
function run(array $command, array $environment, bool $mustPass = true): int
{
    $process = proc_open($command, [
        0 => ['file', '/dev/null', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ], $pipes, null, array_merge($_ENV, $environment));
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start release operation.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    if ($mustPass && $status !== 0) {
        throw new RuntimeException('Release operation failed: ' . trim((string) $stdout . (string) $stderr));
    }
    return $status;
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
