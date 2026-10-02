#!/usr/bin/env php
<?php

declare(strict_types=1);

if ($argc !== 3) {
    fwrite(STDERR, "Usage: select-renewal-targets.php RELEASES_JSON OUTPUT\n");
    exit(64);
}
[, $input, $output] = $argv;
$pages = json_decode((string) file_get_contents($input), true, 128, JSON_THROW_ON_ERROR);
if (!is_array($pages) || !array_is_list($pages)) {
    throw new RuntimeException('GitHub Releases response is malformed.');
}
$releases = $pages;
if ($pages !== [] && is_array($pages[0] ?? null) && array_is_list($pages[0])) {
    $releases = [];
    foreach ($pages as $page) {
        if (!is_array($page) || !array_is_list($page)) {
            throw new RuntimeException('GitHub Releases pagination response is malformed.');
        }
        array_push($releases, ...$page);
    }
}
$latest = [];
foreach ($releases as $release) {
    if (!is_array($release) || !is_string($release['tag_name'] ?? null)
        || !is_bool($release['draft'] ?? null) || !is_bool($release['prerelease'] ?? null)
        || !is_array($release['assets'] ?? null) || !array_is_list($release['assets'])) {
        throw new RuntimeException('GitHub Releases response contains a malformed release.');
    }
    if ($release['draft'] || $release['prerelease']) {
        continue;
    }
    $tag = (string) ($release['tag_name'] ?? '');
    $version = str_starts_with($tag, 'v') ? substr($tag, 1) : '';
    if (!preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $version)) {
        continue;
    }
    $assetNames = [];
    foreach ($release['assets'] as $asset) {
        if (!is_array($asset) || !is_string($asset['name'] ?? null)
            || !is_string($asset['browser_download_url'] ?? null)) {
            throw new RuntimeException("Release {$tag} contains a malformed asset.");
        }
        $assetNames[] = $asset['name'];
    }
    if (!in_array('PortalDots-update-manifest.json', $assetNames, true)) {
        continue;
    }
    $major = explode('.', $version, 2)[0];
    if (!isset($latest[$major]) || version_compare($version, $latest[$major]['version'], '>')) {
        $latest[$major] = ['tag' => $tag, 'version' => $version];
    }
}
ksort($latest, SORT_NATURAL);
$lines = array_map(static fn (array $entry): string => $entry['tag'], array_values($latest));
if (file_put_contents($output, implode("\n", $lines) . ($lines === [] ? '' : "\n")) === false) {
    throw new RuntimeException('Cannot write renewal targets.');
}
