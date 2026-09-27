#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\ReleaseCandidateSelector;

if ($argc !== 5) {
    fwrite(STDERR, "Usage: discover-release-candidates.php RELEASES_JSON TARGET_VERSION ROOT_PUBLIC_KEY OUTPUT\n");
    exit(64);
}
[, $releasesPath, $targetVersion, $rootPublicKey, $output] = $argv;
$releases = json_decode((string) file_get_contents($releasesPath), true, 128, JSON_THROW_ON_ERROR);
if (!is_array($releases)) {
    throw new RuntimeException('GitHub Releases response is not an array.');
}
$selector = new ReleaseCandidateSelector();
$candidates = $selector->select($releases, $targetVersion, $rootPublicKey,
    static fn (string $url): array => downloadJson($url));
if (file_put_contents($output, CanonicalJson::encode($candidates) . "\n") === false) {
    throw new RuntimeException('Cannot write release candidates.');
}
fwrite(STDOUT, $candidates === []
    ? "No prior updater metadata exists; this release is an automatic bootstrap.\n"
    : count($candidates) . " updater source release(s) selected.\n");

/** @return array<string, mixed> */
function downloadJson(string $url): array
{
    $allowed = ['github.com', 'release-assets.githubusercontent.com', 'objects.githubusercontent.com'];
    for ($redirects = 0; $redirects <= 3; $redirects++) {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https'
            || !in_array(strtolower((string) ($parts['host'] ?? '')), $allowed, true)
            || isset($parts['user']) || isset($parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            throw new RuntimeException('Candidate manifest URL is not allowed.');
        }
        $body = '';
        $headers = [];
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'PortalDots-Release-Candidate-Discovery/1',
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$headers): int {
                if (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $headers[strtolower(trim($name))] = trim($value);
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$body): int {
                if (strlen($body) + strlen($chunk) > 16_777_216) {
                    return 0;
                }
                $body .= $chunk;
                return strlen($chunk);
            },
        ]);
        if (!curl_exec($curl)) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException('Cannot download candidate manifest: ' . $error);
        }
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);
        if ($status >= 300 && $status < 400 && isset($headers['location'])) {
            $url = $headers['location'];
            continue;
        }
        if ($status !== 200) {
            throw new RuntimeException("Candidate manifest download returned HTTP {$status}.");
        }
        $decoded = json_decode($body, true, 128, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('Candidate manifest is not a JSON object.');
        }
        return $decoded;
    }
    throw new RuntimeException('Candidate manifest redirected too many times.');
}
