#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\Config;
use PortalDots\Updater\FileBackup;
use PortalDots\Updater\StateStore;
use PortalDots\Updater\ZipPackage;

if ($argc !== 2 && $argc !== 3) {
    fwrite(STDERR, "Usage: artifact-cycle.php SIGNED_JSON [UPDATE_ZIP]\n");
    exit(64);
}
$signed = json_decode((string) file_get_contents($argv[1]), true, 128, JSON_THROW_ON_ERROR);
$archive = $argv[2] ?? dirname($argv[1]) . '/' . basename(parse_url($signed['artifact']['url'], PHP_URL_PATH));
$root = sys_get_temp_dir() . '/portaldots-artifact-cycle-' . bin2hex(random_bytes(8));
mkdir($root . '/storage/app/updater/private', 0700, true);
mkdir($root . '/updater', 0700, true);
try {
    $config = new Config($root, $root . '/storage/app/updater/private', 'https://example.test/manifest',
        ['example.test'], $root . '/missing.pub', maxArchiveBytes: max(filesize($archive) * 2, 1024),
        maxExtractedBytes: max(array_sum(array_column($signed['files'], 'size')) * 2, 1024));
    $package = new ZipPackage($config);
    $package->inspect($archive, $signed['files']);
    $extract = [];
    $staging = $root . '/storage/app/updater/private/staging';
    if (!$package->extractUntil($archive, $staging, $signed['files'], $extract, microtime(true) + 300)) {
        throw new RuntimeException('Exact artifact extraction exceeded the verification time budget.');
    }

    $changed = $signed['files'][0];
    $oldPath = $changed['path'];
    mkdir(dirname($root . '/' . $oldPath), 0700, true);
    $oldContents = (string) file_get_contents($staging . '/' . $oldPath) . "\nold-release-fixture";
    file_put_contents($root . '/' . $oldPath, $oldContents);
    file_put_contents($root . '/updater/runtime.php', 'stable recovery runtime');
    $oldFiles = [[
        'path' => $oldPath,
        'sha256' => hash('sha256', $oldContents),
        'size' => strlen($oldContents),
        'mode' => $changed['mode'],
    ], [
        'path' => 'updater/runtime.php',
        'sha256' => hash_file('sha256', $root . '/updater/runtime.php'),
        'size' => filesize($root . '/updater/runtime.php'),
        'mode' => 0644,
    ]];
    $oldManifest = ['schema' => 1, 'version' => '0.0.1', 'sequence' => 0, 'files' => $oldFiles];
    file_put_contents($root . '/.portaldots-manifest.json', CanonicalJson::encode($oldManifest));
    $store = new StateStore($config);
    $files = new FileBackup($config);
    $files->verifyInstallation($oldManifest);
    $jobId = str_repeat('a', 32);
    $job = $store->jobPath($jobId);
    mkdir($job, 0700, true);
    $plan = $files->plan($oldManifest, $signed['files'], $signed['target_version'], $signed['sequence'], $store, $jobId);
    $context = [];
    while (!$files->backupStep($plan, $context, $job)) {
    }
    $context = [];
    while (!$files->applyStep($plan, $context, $staging, $job)) {
    }
    if (!hash_equals($changed['sha256'], (string) hash_file('sha256', $root . '/' . $oldPath))) {
        throw new RuntimeException('Exact artifact was not applied.');
    }
    $context = [];
    while (!$files->restoreStep($plan, $context, $job)) {
    }
    if (!hash_equals(hash('sha256', $oldContents), (string) hash_file('sha256', $root . '/' . $oldPath))) {
        throw new RuntimeException('Old file was not restored.');
    }
    foreach ($signed['files'] as $file) {
        if ($file['path'] !== $oldPath && file_exists($root . '/' . $file['path'])) {
            throw new RuntimeException('New artifact file remained after rollback: ' . $file['path']);
        }
    }
    fwrite(STDOUT, "exact release artifact apply and rollback verified\n");
} finally {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) {
        $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($root);
}
