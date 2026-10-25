#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

if ($argc !== 4) {
    fwrite(STDERR, "Usage: verify-release-candidate.php CANDIDATES_JSON VERSION FULL_ZIP\n");
    exit(64);
}
[, $candidatesPath, $version, $archive] = $argv;
$candidates = json_decode((string) file_get_contents($candidatesPath), true, 64, JSON_THROW_ON_ERROR);
$candidate = null;
foreach (is_array($candidates) ? $candidates : [] as $entry) {
    if (is_array($entry) && ($entry['version'] ?? null) === $version) {
        $candidate = $entry;
        break;
    }
}
$artifact = is_array($candidate) ? ($candidate['full_artifact'] ?? null) : null;
if (!is_array($artifact) || !is_file($archive)
    || (int) ($artifact['size'] ?? -1) !== filesize($archive)
    || !hash_equals((string) ($artifact['sha256'] ?? ''), (string) hash_file('sha256', $archive))) {
    throw new RuntimeException('Old full release ZIP does not match its root-signed metadata.');
}
fwrite(STDOUT, "old release artifact verified\n");
