#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\ZipPackage;

if ($argc !== 3) {
    fwrite(STDERR, "Usage: build-local-engine-fixture.php SOURCE OUTPUT\n");
    exit(64);
}
$source = realpath($argv[1]);
$output = rtrim($argv[2], '/');
if ($source === false || file_exists($output)) {
    throw new RuntimeException('Fixture paths are invalid.');
}
$dist = $output . '/dist';
mkdir($dist, 0700, true);
$excluded = [
    '.git/', '.github/', 'node_modules/', 'tests/', 'docker_dev/', '.circleci/', 'storage/',
    'bootstrap/cache/',
];
$excludedFiles = [
    '.env', 'CLAUDE.md', 'phpunit.xml', 'phpcs.xml', '.env.testing', '.editorconfig', '.gitignore',
    '.gitattributes', '.prettierrc', 'eslint.config.js',
];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($source) + 1));
    $excludedByPrefix = false;
    foreach ($excluded as $prefix) {
        if (str_starts_with($relative, $prefix)) {
            $excludedByPrefix = true;
            break;
        }
    }
    if (!$file->isFile() || $file->isLink() || in_array($relative, $excludedFiles, true)
        || $excludedByPrefix) {
        continue;
    }
    $target = $dist . '/' . $relative;
    if (!is_dir(dirname($target))) {
        mkdir(dirname($target), 0700, true);
    }
    if (!copy($file->getPathname(), $target)) {
        throw new RuntimeException("Cannot copy {$relative}.");
    }
}
foreach (['storage/app/updater/private', 'storage/framework/cache', 'storage/framework/sessions',
    'storage/framework/views', 'storage/logs', 'bootstrap/cache'] as $directory) {
    mkdir($dist . '/' . $directory, 0700, true);
}

setVersion($dist, '6.0.0');
$oldFiles = records($dist);
file_put_contents($dist . '/.portaldots-manifest.json', CanonicalJson::encode([
    'schema' => 1,
    'version' => '6.0.0',
    'sequence' => 1,
    'files' => $oldFiles,
]) . "\n");
mkdir($output . '/assets', 0700, true);
$oldArchiveFiles = listFiles($dist, false);
$oldArchiveFiles[] = '.portaldots-manifest.json';
sort($oldArchiveFiles, SORT_STRING);
createZip($output . '/assets/PortalDots-old.zip', $dist, $oldArchiveFiles, [
    'bootstrap/cache/',
    'storage/app/public/',
    'storage/app/updater/private/',
    'storage/framework/cache/data/',
    'storage/framework/sessions/',
    'storage/framework/views/',
    'storage/logs/',
]);

setVersion($dist, '6.0.1');
$newFiles = array_values(array_filter(records($dist), static fn (array $file): bool =>
    !ZipPackage::isProtectedPath($file['path'])));
createZip($output . '/assets/PortalDots-update-2.zip', $dist, array_column($newFiles, 'path'));
$archive = $output . '/assets/PortalDots-update-2.zip';
$signed = [
    'schema' => 1,
    'sequence' => 2,
    'issued_at' => gmdate(DATE_ATOM, time() - 60),
    'expires_at' => gmdate(DATE_ATOM, time() + 3600),
    'target_version' => '6.0.1',
    'from_versions' => ['6.0.0'],
    'minimum_php' => '8.3.0',
    'minimum_mysql' => '8.4.0',
    'minimum_runtime' => 1,
    'artifact' => [
        'url' => 'https://example.test/PortalDots-update-2.zip',
        'size' => filesize($archive),
        'sha256' => hash_file('sha256', $archive),
    ],
    'files' => $newFiles,
    'migrations' => [],
    'next_keys' => [],
    'retired_keys' => [],
];
file_put_contents($output . '/assets/PortalDots-update-signed.json', CanonicalJson::encode($signed) . "\n");
fwrite(STDOUT, count($newFiles) . " update files; manifest "
    . filesize($output . '/assets/PortalDots-update-signed.json') . " bytes\n");

function setVersion(string $root, string $version): void
{
    $path = $root . '/app/ReleaseInfo.php';
    $source = (string) file_get_contents($path);
    $source = preg_replace("/public const VERSION = '[^']+';/", "public const VERSION = '{$version}';", $source, 1, $count);
    if ($count !== 1 || file_put_contents($path, $source) === false) {
        throw new RuntimeException('Cannot set fixture version.');
    }
}

/** @return list<array{path: string, sha256: string, size: int, mode: int}> */
function records(string $root): array
{
    $result = [];
    foreach (listFiles($root, true) as $relative) {
        $path = $root . '/' . $relative;
        $result[] = [
            'path' => $relative,
            'sha256' => hash_file('sha256', $path),
            'size' => filesize($path),
            'mode' => fileperms($path) & 0777,
        ];
    }
    return $result;
}

/** @return list<string> */
function listFiles(string $root, bool $skipMutable): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->isLink()) {
            continue;
        }
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if ($relative === '.portaldots-manifest.json' || ($skipMutable && (
            $relative === '.env' || str_starts_with($relative, 'storage/')
            || str_starts_with($relative, 'bootstrap/cache/')
        ))) {
            continue;
        }
        $files[] = $relative;
    }
    sort($files, SORT_STRING);
    return $files;
}

/** @param list<string> $files @param list<string> $directories */
function createZip(string $target, string $root, array $files, array $directories = []): void
{
    $zip = new ZipArchive();
    if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException('Cannot create fixture ZIP.');
    }
    foreach ($directories as $directory) {
        if (!$zip->addEmptyDir(rtrim($directory, '/'))) {
            throw new RuntimeException("Cannot add {$directory}.");
        }
    }
    foreach ($files as $relative) {
        if (!$zip->addFile($root . '/' . $relative, $relative)) {
            throw new RuntimeException("Cannot archive {$relative}.");
        }
    }
    if (!$zip->close()) {
        throw new RuntimeException('Cannot finalize fixture ZIP.');
    }
}
