<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class Downloader
{
    public function __construct(
        private readonly Config $config,
        private readonly ManifestVerifier $verifier,
    ) {
    }

    /** @return array<string, mixed> */
    public function json(string $url, int $maxBytes = 16_777_216): array
    {
        $temporary = tempnam($this->config->privatePath, 'manifest-');
        if ($temporary === false) {
            throw new RuntimeException('一時ファイルを作成できません。');
        }
        try {
            $this->download($url, $temporary, $maxBytes);
            $contents = file_get_contents($temporary);
            if (!is_string($contents)) {
                throw new RuntimeException('更新マニフェストを読み取れません。');
            }
            $decoded = json_decode($contents, true, 128, JSON_THROW_ON_ERROR);
            if (!is_array($decoded)) {
                throw new RuntimeException('更新マニフェストがJSONオブジェクトではありません。');
            }
            return $decoded;
        } finally {
            @unlink($temporary);
        }
    }

    public function download(string $url, string $destination, int $maxBytes): void
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('更新取得に必要な cURL 拡張がありません。');
        }
        for ($redirects = 0; $redirects <= 3; $redirects++) {
            $this->verifier->assertAllowedUrl($url);
            $temporary = $destination . '.part';
            $handle = fopen($temporary, 'wb');
            if ($handle === false) {
                throw new RuntimeException('更新ファイルの保存先を開けません。');
            }
            @chmod($temporary, 0600);
            $received = 0;
            $headers = [];
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_USERAGENT => 'PortalDots-Updater/' . Config::RUNTIME_VERSION,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$headers): int {
                    $length = strlen($line);
                    if (str_contains($line, ':')) {
                        [$name, $value] = explode(':', $line, 2);
                        $headers[strtolower(trim($name))] = trim($value);
                    }
                    return $length;
                },
                CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use ($handle, &$received, $maxBytes): int {
                    $received += strlen($chunk);
                    if ($received > $maxBytes) {
                        return 0;
                    }
                    $written = fwrite($handle, $chunk);
                    return $written === false ? 0 : $written;
                },
            ]);
            $ok = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            fflush($handle);
            fclose($handle);

            if ($status >= 300 && $status < 400 && isset($headers['location'])) {
                @unlink($temporary);
                $url = $this->resolveRedirect($url, $headers['location']);
                continue;
            }
            if ($ok === false || $status !== 200 || $received > $maxBytes) {
                @unlink($temporary);
                throw new RuntimeException('更新ファイルを取得できません: HTTP ' . $status
                    . ($error !== '' ? ' (' . $error . ')' : ''));
            }
            if (!rename($temporary, $destination)) {
                @unlink($temporary);
                throw new RuntimeException('取得した更新ファイルを確定できません。');
            }
            return;
        }
        throw new RuntimeException('更新取得のリダイレクト回数が上限を超えました。');
    }

    /**
     * Download at most one bounded range. Returns true when the complete file is durable.
     *
     * @param array<string, mixed> $context
     */
    public function downloadChunk(string $url, string $destination, int $expectedBytes, array &$context): bool
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('更新取得に必要な cURL 拡張がありません。');
        }
        $partial = $destination . '.part';
        $offset = is_file($partial) ? filesize($partial) : 0;
        if (!is_int($offset) || $offset < 0 || $offset > $expectedBytes) {
            throw new RuntimeException('更新ZIPの部分取得状態が不正です。');
        }
        if ($offset === $expectedBytes) {
            if (!rename($partial, $destination)) {
                throw new RuntimeException('取得した更新ZIPを確定できません。');
            }
            return true;
        }
        $end = min($expectedBytes - 1, $offset + 4_194_303);
        $currentUrl = $url;
        for ($redirects = 0; $redirects <= 3; $redirects++) {
            $this->verifier->assertAllowedUrl($currentUrl);
            $headers = [];
            $received = 0;
            $handle = fopen($partial, 'c+b');
            if ($handle === false || !flock($handle, LOCK_EX) || fseek($handle, $offset) !== 0) {
                if (is_resource($handle)) {
                    fclose($handle);
                }
                throw new RuntimeException('更新ZIPの部分ファイルを開けません。');
            }
            @chmod($partial, 0600);
            $curl = curl_init($currentUrl);
            $requestHeaders = ['Accept-Encoding: identity'];
            if (isset($context['etag'])) {
                $requestHeaders[] = 'If-Range: ' . $context['etag'];
            }
            curl_setopt_array($curl, [
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_USERAGENT => 'PortalDots-Updater/' . Config::RUNTIME_VERSION,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_RANGE => $offset . '-' . $end,
                CURLOPT_HTTPHEADER => $requestHeaders,
                CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$headers): int {
                    if (str_contains($line, ':')) {
                        [$name, $value] = explode(':', $line, 2);
                        $headers[strtolower(trim($name))] = trim($value);
                    }
                    return strlen($line);
                },
                CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use ($handle, &$received, $end, $offset): int {
                    if ($received + strlen($chunk) > ($end - $offset + 1)) {
                        return 0;
                    }
                    $written = fwrite($handle, $chunk);
                    if ($written !== false) {
                        $received += $written;
                    }
                    return $written === false ? 0 : $written;
                },
            ]);
            $ok = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            $durable = fflush($handle) && (!function_exists('fsync') || fsync($handle));
            flock($handle, LOCK_UN);
            fclose($handle);

            if ($status >= 300 && $status < 400 && isset($headers['location'])) {
                $truncate = fopen($partial, 'c+b');
                if ($truncate !== false) {
                    ftruncate($truncate, $offset);
                    fclose($truncate);
                }
                $currentUrl = $this->resolveRedirect($currentUrl, $headers['location']);
                continue;
            }
            $expectedStatus = $expectedBytes <= 4_194_304 && $offset === 0 ? [200, 206] : [206];
            $contentRange = $headers['content-range'] ?? '';
            if ($ok === false || !in_array($status, $expectedStatus, true) || !$durable
                || $received !== ($end - $offset + 1)
                || ($status === 206 && !preg_match('#^bytes ' . $offset . '-' . $end . '/' . $expectedBytes . '$#', $contentRange))) {
                $truncate = fopen($partial, 'c+b');
                if ($truncate !== false) {
                    ftruncate($truncate, $offset);
                    fclose($truncate);
                }
                throw new RuntimeException('更新ZIPの部分取得に失敗しました: HTTP ' . $status
                    . ($error !== '' ? ' (' . $error . ')' : ''));
            }
            if (isset($headers['etag'])) {
                if (isset($context['etag']) && !hash_equals((string) $context['etag'], $headers['etag'])) {
                    throw new RuntimeException('更新ZIPが取得途中で変更されました。');
                }
                $context['etag'] = $headers['etag'];
            }
            $context['downloaded'] = $offset + $received;
            if ($context['downloaded'] === $expectedBytes) {
                if (!rename($partial, $destination)) {
                    throw new RuntimeException('取得した更新ZIPを確定できません。');
                }
                return true;
            }
            return false;
        }
        throw new RuntimeException('更新ZIP取得のリダイレクト回数が上限を超えました。');
    }

    private function resolveRedirect(string $current, string $location): string
    {
        if (str_starts_with($location, 'https://')) {
            return $location;
        }
        $parts = parse_url($current);
        if (str_starts_with($location, '/') && isset($parts['host'])) {
            return 'https://' . $parts['host'] . $location;
        }
        throw new RuntimeException('更新取得先が不正な相対URLへリダイレクトしました。');
    }
}
