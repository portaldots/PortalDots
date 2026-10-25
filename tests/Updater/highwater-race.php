#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\Config;
use PortalDots\Updater\ManifestHighwater;

if (($argv[1] ?? null) === '--child') {
    [, , $root, $name] = $argv;
    file_put_contents($root . "/ready-{$name}", '1');
    while (!is_file($root . '/go')) {
        usleep(1_000);
    }
    $verified = json_decode((string) file_get_contents($root . "/{$name}.json"), true, 32,
        JSON_THROW_ON_ERROR);
    try {
        (new ManifestHighwater(config($root)))->observe($verified);
        exit(0);
    } catch (Throwable) {
        exit(1);
    }
}

$root = sys_get_temp_dir() . '/portaldots-highwater-' . bin2hex(random_bytes(8));
mkdir($root . '/private', 0700, true);
try {
    $base = [
        'signed' => ['sequence' => 5, 'target_version' => '6.0.5'],
        'root_digest' => str_repeat('a', 64),
        'lease_sequence' => 900,
    ];
    file_put_contents($root . '/a.json', json_encode($base + ['lease_digest' => str_repeat('b', 64)]));
    file_put_contents($root . '/b.json', json_encode($base + ['lease_digest' => str_repeat('c', 64)]));
    $processes = [];
    foreach (['a', 'b'] as $name) {
        $processes[$name] = proc_open([PHP_BINARY, __FILE__, '--child', $root, $name], [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', '/dev/null', 'w'],
        ], $pipes);
    }
    $deadline = microtime(true) + 5;
    while ((!is_file($root . '/ready-a') || !is_file($root . '/ready-b')) && microtime(true) < $deadline) {
        usleep(10_000);
    }
    if (!is_file($root . '/ready-a') || !is_file($root . '/ready-b')) {
        throw new RuntimeException('Highwater race children did not become ready.');
    }
    touch($root . '/go');
    $statuses = [];
    foreach ($processes as $process) {
        $statuses[] = is_resource($process) ? proc_close($process) : 2;
    }
    sort($statuses);
    if ($statuses !== [0, 1]) {
        throw new RuntimeException('Concurrent same-sequence leases were not serialized safely.');
    }
    $highest = (new ManifestHighwater(config($root)))->read(6);
    if (!in_array($highest['lease_digest'], [str_repeat('b', 64), str_repeat('c', 64)], true)) {
        throw new RuntimeException('Winning lease was not persisted.');
    }
    fwrite(STDOUT, "highwater race serialized\n");
} finally {
    removeTree($root);
}

function config(string $root): Config
{
    return new Config($root, $root . '/private', 'https://example.test/manifest', ['example.test'],
        $root . '/missing.pub');
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
