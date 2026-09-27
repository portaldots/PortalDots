#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\StateStore;
use PortalDots\Updater\Config;

if (($argc !== 3 && $argc !== 4) || !in_array($argv[2], ['updating', 'restoring', 'completed', 'rolled_back'], true)) {
    fwrite(STDERR, "Usage: ui-fixture.php TARGET_DIRECTORY updating|restoring|completed|rolled_back [BASE_URL]\n");
    exit(64);
}

$sourceRoot = dirname(__DIR__, 2);
$root = rtrim($argv[1], DIRECTORY_SEPARATOR);
$mode = $argv[2];
if (file_exists($root)) {
    throw new RuntimeException('Fixture target already exists.');
}
mkdir($root . '/public', 0700, true);
mkdir($root . '/storage/app/updater/private', 0700, true);
symlink($sourceRoot . '/updater', $root . '/updater');
copy($sourceRoot . '/public/updater.php', $root . '/public/updater.php');
$baseUrl = $argv[3] ?? 'http://127.0.0.1:18848';
file_put_contents($root . '/.env', "APP_URL={$baseUrl}\nPORTALDOTS_UPDATER_ALLOW_INSECURE_LOCALHOST=1\n");

$config = new Config(
    $root,
    $root . '/storage/app/updater/private',
    'https://example.test/manifest',
    ['example.test'],
    $root . '/updater/keys/release.pub',
);
$store = new StateStore($config);
$id = bin2hex(random_bytes(16));
$code = '01234567-89ABCDEF-01234567-89ABCDEF';
$phase = $mode;
$currentStep = match ($mode) {
    'updating' => 'backup_database',
    'restoring' => 'migrate_database',
    default => 'finalize',
};
$state = [
    'schema' => 1,
    'id' => $id,
    'phase' => $phase,
    'current_step' => $currentStep,
    'step_status' => $mode === 'restoring' ? 'pending' : 'running',
    'from_version' => '6.0.0',
    'target_version' => '6.0.1',
    'started_at' => gmdate(DATE_ATOM, time() - 120),
    'updated_at' => gmdate(DATE_ATOM),
    'actor' => ['id' => 1, 'email' => 'admin@example.test'],
    'recovery_hash' => password_hash($code, PASSWORD_BCRYPT),
    'auth_failures' => 0,
    'auth_locked_until' => null,
    'sessions' => [],
    'history' => [],
    'last_error' => $mode === 'restoring' ? 'DB移行の完了状態を確認できないため、自動復元します。' : null,
];
if ($mode === 'restoring') {
    $state['restore'] = [
        'step' => 'restore_database',
        'paused' => true,
        'last_error' => '復元処理は診断用fixtureで一時停止しています。',
    ];
}
$store->create($state);
fwrite(STDOUT, json_encode([
    'document_root' => $root . '/public',
    'job_id' => $id,
    'mode' => $mode,
    'recovery_code' => $code,
    'url' => rtrim($baseUrl, '/') . '/updater.php',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
