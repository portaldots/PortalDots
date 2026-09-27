#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\ZipPackage;

if ($argc !== 6) {
    fwrite(STDERR, "Usage: build-release.php DIST OUTPUT VERSION SEQUENCE PUBLIC_KEY_BASE64\n");
    exit(64);
}
[, $dist, $output, $version, $sequenceText, $publicKey] = $argv;
$dist = rtrim($dist, '/');
$output = rtrim($output, '/');
$sequence = filter_var($sequenceText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!is_dir($dist) || $sequence === false
    || !preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $version)) {
    throw new RuntimeException('Build arguments are invalid.');
}
$decodedPublicKey = base64_decode($publicKey, true);
if (!is_string($decodedPublicKey) || strlen($decodedPublicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
    throw new RuntimeException('UPDATER_PUBLIC_KEY must be a base64 Ed25519 public key.');
}
if (!is_dir($output) && !mkdir($output, 0755, true) && !is_dir($output)) {
    throw new RuntimeException('Cannot create output directory.');
}
file_put_contents($dist . '/updater/keys/release.pub', $publicKey . "\n");

$allFiles = listFiles($dist);
$installedFiles = [];
foreach ($allFiles as $relative) {
    if (isMutableRuntimePath($relative) || $relative === '.portaldots-manifest.json') {
        continue;
    }
    $installedFiles[] = fileRecord($dist, $relative);
}
file_put_contents($dist . '/.portaldots-manifest.json', CanonicalJson::encode([
    'schema' => 1,
    'version' => $version,
    'sequence' => $sequence,
    'files' => $installedFiles,
]) . "\n");

$fullFiles = listFiles($dist);
createZip($output . '/PortalDots.zip', $dist, $fullFiles, [
    'bootstrap/cache/',
    'storage/app/public/',
    'storage/app/updater/private/',
    'storage/framework/cache/data/',
    'storage/framework/sessions/',
    'storage/framework/views/',
    'storage/logs/',
]);

$updateFiles = [];
foreach ($installedFiles as $file) {
    if (!ZipPackage::isProtectedPath($file['path'])) {
        $updateFiles[] = $file;
    }
}
$updateArchiveName = 'PortalDots-update-' . $sequence . '.zip';
createZip($output . '/' . $updateArchiveName, $dist, array_column($updateFiles, 'path'));
$archiveHash = hash_file('sha256', $output . '/' . $updateArchiveName);
$archiveSize = filesize($output . '/' . $updateArchiveName);
if (!is_string($archiveHash) || !is_int($archiveSize)) {
    throw new RuntimeException('Cannot hash update archive.');
}

$major = explode('.', $version, 2)[0];
$fromVersions = discoverFromVersions($major, $version);
$migrations = migrationContracts($dist, $fromVersions);
$issued = new DateTimeImmutable('now', new DateTimeZone('UTC'));
$signed = [
    'schema' => 1,
    'sequence' => $sequence,
    'issued_at' => $issued->format(DATE_ATOM),
    'expires_at' => $issued->modify('+30 days')->format(DATE_ATOM),
    'target_version' => $version,
    'from_versions' => $fromVersions,
    'minimum_php' => '8.3.0',
    'minimum_mysql' => '8.4.0',
    'minimum_runtime' => 1,
    'artifact' => [
        'url' => "https://github.com/portaldots/PortalDots/releases/download/v{$version}/{$updateArchiveName}",
        'size' => $archiveSize,
        'sha256' => $archiveHash,
    ],
    'files' => $updateFiles,
    'migrations' => $migrations,
    'next_keys' => [],
    'retired_keys' => [],
];
file_put_contents($output . '/PortalDots-update-signed.json', CanonicalJson::encode($signed) . "\n");

/** @return list<string> */
function listFiles(string $root): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->isLink()) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        ZipPackage::normalizePath($relative);
        $files[] = $relative;
    }
    sort($files, SORT_STRING);
    return $files;
}

/** @return array{path: string, sha256: string, size: int, mode: int} */
function fileRecord(string $root, string $relative): array
{
    $path = $root . '/' . $relative;
    $hash = hash_file('sha256', $path);
    $size = filesize($path);
    if (!is_string($hash) || !is_int($size)) {
        throw new RuntimeException("Cannot inspect {$relative}.");
    }
    return ['path' => $relative, 'sha256' => $hash, 'size' => $size, 'mode' => fileperms($path) & 0777];
}

/** @param list<string> $files @param list<string> $directories */
function createZip(string $target, string $root, array $files, array $directories = []): void
{
    $zip = new ZipArchive();
    if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("Cannot create {$target}.");
    }
    foreach ($directories as $directory) {
        if (!$zip->addEmptyDir(rtrim($directory, '/'))) {
            $zip->close();
            throw new RuntimeException("Cannot add {$directory} to archive.");
        }
    }
    foreach ($files as $relative) {
        if (!$zip->addFile($root . '/' . $relative, $relative)) {
            $zip->close();
            throw new RuntimeException("Cannot add {$relative} to archive.");
        }
    }
    if (!$zip->close()) {
        throw new RuntimeException("Cannot finalize {$target}.");
    }
}

function isMutableRuntimePath(string $path): bool
{
    return $path === '.env' || str_starts_with($path, 'storage/')
        || str_starts_with($path, 'bootstrap/cache/')
        || str_starts_with($path, 'public/storage/') || str_starts_with($path, 'public/uploads/');
}

/** @return list<string> */
function discoverFromVersions(string $major, string $target): array
{
    $override = getenv('UPDATER_FROM_VERSIONS');
    if (!is_string($override) || trim($override) === '') {
        if (getenv('UPDATER_BOOTSTRAP_RELEASE') === '1') {
            return [];
        }
        throw new RuntimeException('UPDATER_FROM_VERSIONS must explicitly list supported source releases.');
    }
    $versions = array_values(array_filter(array_map('trim', explode(',', $override))));
    $versions = array_values(array_filter($versions, static function (string $candidate) use ($major, $target): bool {
        return (bool) preg_match('/^' . preg_quote($major, '/') . '\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $candidate)
            && version_compare($candidate, $target, '<');
    }));
    usort($versions, 'version_compare');
    $versions = array_values(array_unique($versions));
    if ($versions === []) {
        throw new RuntimeException('UPDATER_FROM_VERSIONS has no stable same-major source release below target.');
    }
    return $versions;
}

/** @param list<string> $fromVersions @return list<array<string, mixed>> */
function migrationContracts(string $dist, array $fromVersions): array
{
    $contractPath = dirname(__DIR__) . '/migration-contracts.json';
    $contracts = json_decode((string) file_get_contents($contractPath), true, 64, JSON_THROW_ON_ERROR);
    $result = [];
    foreach ($fromVersions as $fromVersion) {
        $command = 'git diff --name-status ' . escapeshellarg('v' . $fromVersion)
            . '..HEAD -- database/migrations';
        exec($command, $changes, $status);
        if ($status !== 0) {
            throw new RuntimeException("Cannot inspect migrations from {$fromVersion}.");
        }
        foreach ($changes as $change) {
            [$statusCode, $path] = array_pad(preg_split('/\s+/', trim($change), 2), 2, null);
            if ($statusCode !== 'A' || !is_string($path)) {
                throw new RuntimeException('Existing migration files must never be modified or removed.');
            }
            $verify = $contracts[$path]['verify'] ?? null;
            if (!is_array($verify) || $verify === []) {
                throw new RuntimeException("Migration {$path} needs non-empty updater verification queries.");
            }
            $source = (string) file_get_contents($dist . '/' . $path);
            if (preg_match('/\b(Http::|Mail::|Storage::|dispatch\s*\(|curl_|exec\s*\(|shell_exec|file_put_contents|unlink\s*\(|Schema::connection|DB::connection)/i', $source)) {
                throw new RuntimeException("Migration {$path} contains an external or filesystem side effect.");
            }
            $result[$path] ??= [
                'path' => $path,
                'sha256' => hash_file('sha256', $dist . '/' . $path),
                'verify' => array_values($verify),
                'from_versions' => [],
            ];
            $result[$path]['from_versions'][] = $fromVersion;
        }
        unset($changes);
    }
    ksort($result, SORT_STRING);
    return array_values($result);
}
