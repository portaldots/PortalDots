<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class HttpProbe
{
    private const HEADER = 'HTTP_X_PORTALDOTS_UPDATER_PROBE';
    private const FILE = 'http-probe.json';

    public function __construct(private readonly Config $config)
    {
    }

    public static function respondIfRequested(Config $config): bool
    {
        $nonce = $_SERVER[self::HEADER] ?? null;
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST'
            || !is_string($nonce) || !preg_match('/^[a-f0-9]{64}$/', $nonce)) {
            return false;
        }
        header('Content-Type: text/plain; charset=UTF-8');
        header('Cache-Control: no-store');
        $record = self::readRecord($config->privatePath . '/' . self::FILE);
        if (!hash_equals((string) ($record['nonce'] ?? ''), $nonce)
            || (int) ($record['expires_at'] ?? 0) < time()) {
            http_response_code(404);
            echo "Not Found\n";
            return true;
        }
        echo self::response($nonce);
        return true;
    }

    public function verifyPrivateStorage(): void
    {
        $baseUrl = $this->validatedBaseUrl();
        $nonce = bin2hex(random_bytes(32));
        $path = $this->config->privatePath . '/' . self::FILE;
        $record = CanonicalJson::encode([
            'expires_at' => time() + 120,
            'nonce' => $nonce,
        ]) . "\n";
        $this->writeProbe($path, $record);
        try {
            [$status, $body] = $this->request(
                $baseUrl . '/updater.php',
                'POST',
                ['X-PortalDots-Updater-Probe: ' . $nonce],
            );
            if ($status !== 200 || !hash_equals(self::response($nonce), $body)) {
                throw new RuntimeException('このPortalDots設置先へHTTPSで到達できることを確認できません。');
            }

            $relative = $this->relativePrivatePath();
            if ($relative !== null) {
                foreach ([$relative, 'public/' . $relative] as $candidate) {
                    [$privateStatus, $privateBody] = $this->request(
                        $baseUrl . '/' . implode('/', array_map('rawurlencode', explode('/', $candidate))),
                        'GET',
                    );
                    if (!in_array($privateStatus, [403, 404], true)
                        || str_contains($privateBody, $nonce)) {
                        throw new RuntimeException('更新用バックアップ領域がWebから非公開であることを確認できません。');
                    }
                }
            }
        } finally {
            if (is_file($path) && !unlink($path)) {
                throw new RuntimeException('HTTP非公開性診断の一時ファイルを削除できません。');
            }
        }
    }

    public function canonicalUpdaterPath(): string
    {
        $base = $this->validatedBaseUrl();
        $path = (string) (parse_url($base, PHP_URL_PATH) ?? '');
        return '/' . (trim($path, '/') === '' ? '' : trim($path, '/') . '/') . 'updater.php';
    }

    private function validatedBaseUrl(): string
    {
        $url = rtrim((string) $this->config->applicationUrl, '/');
        $parts = parse_url($url);
        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new RuntimeException('APP_URLが更新元URLとして不正です。');
        }
        $scheme = strtolower((string) $parts['scheme']);
        $host = strtolower((string) $parts['host']);
        $local = in_array($host, ['127.0.0.1', '::1', 'localhost'], true);
        if ($scheme !== 'https' && !($scheme === 'http' && $local && $this->config->allowInsecureLocalhost)) {
            throw new RuntimeException('ブラウザ更新にはHTTPSのAPP_URLが必要です。');
        }

        $requestHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $requestScheme = $this->requestScheme();
        $expectedAuthority = $host . (isset($parts['port']) ? ':' . $parts['port'] : '');
        if ($requestScheme !== $scheme || !hash_equals($expectedAuthority, $requestHost)) {
            throw new RuntimeException('APP_URLと現在の更新画面の接続先が一致しません。');
        }
        $path = isset($parts['path']) ? '/' . trim((string) $parts['path'], '/') : '';
        return $scheme . '://' . $expectedAuthority . rtrim($path, '/');
    }

    private function requestScheme(): string
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
        return ($https !== '' && $https !== 'off') || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
            ? 'https' : 'http';
    }

    private function relativePrivatePath(): ?string
    {
        $base = realpath($this->config->basePath);
        $private = realpath($this->config->privatePath);
        if ($base === false || $private === false || !str_starts_with($private . '/', $base . '/')) {
            return null;
        }
        return str_replace(DIRECTORY_SEPARATOR, '/', substr($private, strlen($base) + 1)) . '/' . self::FILE;
    }

    /** @param list<string> $headers @return array{int, string} */
    private function request(string $url, string $method, array $headers = []): array
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new RuntimeException('HTTP非公開性診断を初期化できません。');
        }
        $body = '';
        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'PortalDots-Updater/' . Config::RUNTIME_VERSION,
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$body): int {
                if (strlen($body) + strlen($chunk) > 65_536) {
                    return 0;
                }
                $body .= $chunk;
                return strlen($chunk);
            },
        ]);
        $ok = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $error = curl_error($handle);
        curl_close($handle);
        if ($ok === false) {
            throw new RuntimeException('HTTP非公開性診断へ接続できません: ' . ($error !== '' ? '通信エラー' : '応答なし'));
        }
        return [$status, $body];
    }

    /** @return array<string, mixed> */
    private static function readRecord(string $path): array
    {
        if (!is_file($path) || is_link($path)) {
            return [];
        }
        try {
            $decoded = json_decode((string) file_get_contents($path), true, 8, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return [];
        }
        return is_array($decoded) ? $decoded : [];
    }

    private function writeProbe(string $path, string $record): void
    {
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $handle = fopen($temporary, 'xb');
        if ($handle === false || fwrite($handle, $record) !== strlen($record)
            || !fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            @unlink($temporary);
            throw new RuntimeException('HTTP非公開性診断を保存できません。');
        }
        fclose($handle);
        @chmod($temporary, 0600);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('HTTP非公開性診断を確定できません。');
        }
    }

    private static function response(string $nonce): string
    {
        return 'portaldots-updater-probe:' . hash('sha256', "endpoint\0" . $nonce) . "\n";
    }
}
