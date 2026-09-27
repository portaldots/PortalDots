<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use DateTimeImmutable;
use RuntimeException;

final class ManifestVerifier
{
    public function __construct(
        private readonly Config $config,
        private readonly TrustedKeyStore $keys,
    ) {
    }

    /**
     * @param array<string, mixed> $envelope
     * @return array{signed: array<string, mixed>, signing_key_id: string, digest: string}
     */
    public function verify(
        array $envelope,
        string $currentVersion,
        int $highestSequence = 0,
        ?string $highestDigest = null,
    ): array
    {
        if (!extension_loaded('sodium')) {
            throw new RuntimeException('署名検証に必要な sodium 拡張がありません。');
        }
        $signed = $envelope['signed'] ?? null;
        $signatures = $envelope['signatures'] ?? null;
        if (!is_array($signed) || !is_array($signatures) || !array_is_list($signatures)) {
            throw new RuntimeException('更新マニフェストの形式が不正です。');
        }
        $payload = CanonicalJson::encode($signed);
        $sequence = is_int($signed['sequence'] ?? null) ? $signed['sequence'] : 0;
        $trusted = $this->keys->all($sequence);
        if ($trusted === []) {
            throw new RuntimeException('更新署名公開鍵が設定されていません。');
        }
        $signingKeyId = null;
        foreach ($signatures as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            $keyId = (string) ($candidate['keyid'] ?? '');
            $signature = base64_decode((string) ($candidate['sig'] ?? ''), true);
            if (isset($trusted[$keyId]) && is_string($signature)
                && strlen($signature) === SODIUM_CRYPTO_SIGN_BYTES
                && sodium_crypto_sign_verify_detached($signature, $payload, $trusted[$keyId])) {
                $signingKeyId = $keyId;
                break;
            }
        }
        if ($signingKeyId === null) {
            throw new RuntimeException('更新マニフェストの署名を検証できません。');
        }

        $digest = hash('sha256', $payload);
        $this->validateSigned($signed, $currentVersion, $highestSequence);
        if ($sequence === $highestSequence && $highestDigest !== null && !hash_equals($highestDigest, $digest)) {
            throw new RuntimeException('同じ連番で内容が異なる更新マニフェストは使用できません。');
        }
        return [
            'signed' => $signed,
            'signing_key_id' => $signingKeyId,
            'digest' => $digest,
        ];
    }

    /** @param array<string, mixed> $signed */
    private function validateSigned(array $signed, string $currentVersion, int $highestSequence): void
    {
        $required = [
            'schema', 'sequence', 'issued_at', 'expires_at', 'target_version', 'from_versions',
            'minimum_php', 'minimum_mysql', 'minimum_runtime', 'artifact', 'files', 'migrations',
        ];
        foreach ($required as $key) {
            if (!array_key_exists($key, $signed)) {
                throw new RuntimeException("更新マニフェストに {$key} がありません。");
            }
        }
        if ($signed['schema'] !== 1 || !is_int($signed['sequence']) || $signed['sequence'] < 1) {
            throw new RuntimeException('更新マニフェストのスキーマまたは連番が不正です。');
        }
        if ($signed['sequence'] < $highestSequence) {
            throw new RuntimeException('過去の更新マニフェストは使用できません。');
        }

        try {
            $issued = new DateTimeImmutable((string) $signed['issued_at']);
            $expires = new DateTimeImmutable((string) $signed['expires_at']);
        } catch (\Throwable) {
            throw new RuntimeException('更新マニフェストの日時が不正です。');
        }
        $now = new DateTimeImmutable('now');
        if ($expires <= $now || $issued > $now->modify('+5 minutes') || $expires <= $issued) {
            throw new RuntimeException('更新マニフェストが期限外です。');
        }

        $target = (string) $signed['target_version'];
        if (!$this->isStableVersion($target) || !$this->isStableVersion($currentVersion)) {
            throw new RuntimeException('正式リリース以外はブラウザ更新できません。');
        }
        if (version_compare($target, $currentVersion, '<=')) {
            throw new RuntimeException('ダウングレードまたは同一版の再適用はできません。');
        }
        if (explode('.', $target, 2)[0] !== explode('.', $currentVersion, 2)[0]) {
            throw new RuntimeException('異なるメジャーバージョンへは更新できません。');
        }
        if (!is_array($signed['from_versions']) || !in_array($currentVersion, $signed['from_versions'], true)) {
            throw new RuntimeException('現在の版からこの更新版への経路がありません。');
        }
        if (version_compare(PHP_VERSION, (string) $signed['minimum_php'], '<')) {
            throw new RuntimeException('この更新版が要求するPHPバージョンを満たしていません。');
        }
        if ((int) $signed['minimum_runtime'] > Config::RUNTIME_VERSION) {
            throw new RuntimeException('復旧ランタイムの手動更新が必要です。');
        }

        $artifact = $signed['artifact'];
        if (!is_array($artifact) || !is_string($artifact['url'] ?? null)
            || !is_int($artifact['size'] ?? null) || $artifact['size'] < 1
            || $artifact['size'] > $this->config->maxArchiveBytes
            || !preg_match('/^[a-f0-9]{64}$/', (string) ($artifact['sha256'] ?? ''))) {
            throw new RuntimeException('更新ZIPの情報が不正です。');
        }
        $this->assertAllowedUrl($artifact['url']);

        if (!is_array($signed['files']) || $signed['files'] === []) {
            throw new RuntimeException('更新ファイル一覧が空です。');
        }
        $seen = [];
        foreach ($signed['files'] as $file) {
            if (!is_array($file) || !is_string($file['path'] ?? null)
                || !preg_match('/^[a-f0-9]{64}$/', (string) ($file['sha256'] ?? ''))
                || !is_int($file['size'] ?? null) || $file['size'] < 0
                || !is_int($file['mode'] ?? null) || $file['mode'] < 0 || $file['mode'] > 0777) {
                throw new RuntimeException('更新ファイル一覧が不正です。');
            }
            $path = ZipPackage::normalizePath($file['path']);
            if ($path !== $file['path'] || isset($seen[strtolower($path)])) {
                throw new RuntimeException('更新ファイル一覧に重複または不正なパスがあります。');
            }
            if (ZipPackage::isProtectedPath($path)) {
                throw new RuntimeException("更新禁止ファイルが含まれています: {$path}");
            }
            $seen[strtolower($path)] = true;
        }

        if (!is_array($signed['migrations'])) {
            throw new RuntimeException('DB移行一覧が不正です。');
        }
        foreach ($signed['migrations'] as $migration) {
            if (!is_array($migration) || !preg_match('#^database/migrations/[A-Za-z0-9_]+\.php$#',
                (string) ($migration['path'] ?? ''))
                || !preg_match('/^[a-f0-9]{64}$/', (string) ($migration['sha256'] ?? ''))
                || !is_array($migration['verify'] ?? null) || $migration['verify'] === []
                || !is_array($migration['from_versions'] ?? null) || $migration['from_versions'] === []) {
                throw new RuntimeException('DB移行の宣言が不正です。');
            }
            foreach ($migration['from_versions'] as $fromVersion) {
                if (!is_string($fromVersion) || !in_array($fromVersion, $signed['from_versions'], true)) {
                    throw new RuntimeException('DB移行の更新元バージョンが不正です。');
                }
            }
            foreach ($migration['verify'] as $statement) {
                if (!is_string($statement) || !preg_match('/^\s*(SELECT|SHOW)\b/i', $statement)
                    || str_contains($statement, ';')) {
                    throw new RuntimeException('DB移行の事後検証には単一のSELECTまたはSHOWだけを指定できます。');
                }
            }
        }
        if (!is_array($signed['next_keys'] ?? []) || !is_array($signed['retired_keys'] ?? [])) {
            throw new RuntimeException('署名鍵更新情報が不正です。');
        }
    }

    public function assertAllowedUrl(string $url): void
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        if (($parts['scheme'] ?? null) !== 'https' || $host === ''
            || isset($parts['user']) || isset($parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)
            || !in_array($host, array_map('strtolower', $this->config->downloadHosts), true)) {
            throw new RuntimeException('更新ファイルの取得先が許可されていません。');
        }
    }

    private function isStableVersion(string $version): bool
    {
        return (bool) preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $version);
    }
}
