#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use PortalDots\Updater\CanonicalJson;

if ($argc !== 2 && $argc !== 3) {
    fwrite(STDERR, "Usage: verify-release.php SIGNED_JSON [UPDATE_ZIP]\n");
    exit(64);
}
[, $manifestPath] = $argv;
$signed = json_decode((string) file_get_contents($manifestPath), true, 128, JSON_THROW_ON_ERROR);
$archivePath = $argv[2] ?? dirname($manifestPath) . '/' . basename(parse_url($signed['artifact']['url'], PHP_URL_PATH));
if (!hash_equals((string) $signed['artifact']['sha256'], (string) hash_file('sha256', $archivePath))
    || (int) $signed['artifact']['size'] !== filesize($archivePath)) {
    throw new RuntimeException('Artifact hash or size does not match the signed payload.');
}
$zip = new ZipArchive();
if ($zip->open($archivePath, ZipArchive::RDONLY) !== true) {
    throw new RuntimeException('Cannot open update archive.');
}
$actual = [];
for ($index = 0; $index < $zip->numFiles; $index++) {
    $stat = $zip->statIndex($index);
    $stream = $zip->getStream($stat['name']);
    $hash = hash_init('sha256');
    hash_update_stream($hash, $stream);
    fclose($stream);
    $actual[] = ['path' => $stat['name'], 'sha256' => hash_final($hash), 'size' => $stat['size']];
}
$zip->close();
$expected = array_map(static fn (array $file): array => [
    'path' => $file['path'],
    'sha256' => $file['sha256'],
    'size' => $file['size'],
], $signed['files']);
if (!hash_equals(hash('sha256', CanonicalJson::encode($expected)), hash('sha256', CanonicalJson::encode($actual)))) {
    throw new RuntimeException('Archive contents do not match the signed file list.');
}
fwrite(STDOUT, "release artifact verified\n");
