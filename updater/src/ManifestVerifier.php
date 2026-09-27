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

    /** @param array<string, mixed> $envelope @return array<string, mixed> */
    public function verify(
        array $envelope,
        string $currentVersion,
        int $highestSequence = 0,
        ?string $highestDigest = null,
        int $highestLeaseSequence = 0,
        ?string $highestLeaseDigest = null,
    ): array {
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
        $signingKeyId = $this->verifySignatures($payload, $signatures, $this->keys->all($sequence));
        if ($signingKeyId === null) {
            throw new RuntimeException('更新マニフェストの署名を検証できません。');
        }

        $rootDigest = hash('sha256', $payload);
        $this->validateSigned($signed, $currentVersion, $highestSequence);
        if ($sequence === $highestSequence && $highestDigest !== null && !hash_equals($highestDigest, $rootDigest)) {
            throw new RuntimeException('同じ連番で内容が異なる更新マニフェストは使用できません。');
        }

        $leaseSequence = null;
        $leaseDigest = null;
        $confirmationDigest = $rootDigest;
        if ($signed['schema'] === 2) {
            $sameRoot = $sequence === $highestSequence && $highestDigest !== null
                && hash_equals($highestDigest, $rootDigest);
            [$leaseSequence, $leaseDigest] = $this->verifyLease(
                $envelope,
                $signed,
                $rootDigest,
                $sameRoot ? $highestLeaseSequence : 0,
                $sameRoot ? $highestLeaseDigest : null,
            );
            $confirmationDigest = hash('sha256', $rootDigest . ':' . $leaseDigest);
        }

        return [
            'signed' => $signed,
            'signing_key_id' => $signingKeyId,
            'root_digest' => $rootDigest,
            'lease_sequence' => $leaseSequence,
            'lease_digest' => $leaseDigest,
            'digest' => $confirmationDigest,
        ];
    }

    /** @param list<array<string, mixed>> $signatures @param array<string, string> $trusted */
    private function verifySignatures(string $payload, array $signatures, array $trusted): ?string
    {
        if ($trusted === []) {
            throw new RuntimeException('更新署名公開鍵が設定されていません。');
        }
        foreach ($signatures as $candidate) {
            if (!is_array($candidate)) {
                continue;
            }
            $keyId = (string) ($candidate['keyid'] ?? '');
            $signature = base64_decode((string) ($candidate['sig'] ?? ''), true);
            if (isset($trusted[$keyId]) && is_string($signature)
                && strlen($signature) === SODIUM_CRYPTO_SIGN_BYTES
                && sodium_crypto_sign_verify_detached($signature, $payload, $trusted[$keyId])) {
                return $keyId;
            }
        }
        return null;
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
        if (!in_array($signed['schema'], [1, 2], true)
            || !is_int($signed['sequence']) || $signed['sequence'] < 1) {
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
        if ($issued > $now->modify('+5 minutes') || $expires <= $issued
            || ($signed['schema'] === 1 && $expires <= $now)) {
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

        $this->validateArtifact($signed['artifact'], '更新ZIP');
        if ($signed['schema'] === 2) {
            $this->validateArtifact($signed['full_artifact'] ?? null, '配布ZIP');
            $this->validateFreshness($signed['freshness'] ?? null);
        }

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
        $this->validateMigrations($signed);
        if (!is_array($signed['next_keys'] ?? []) || !is_array($signed['retired_keys'] ?? [])) {
            throw new RuntimeException('署名鍵更新情報が不正です。');
        }
    }

    /** @param array<string, mixed> $signed */
    private function validateMigrations(array $signed): void
    {
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
    }

    private function validateArtifact(mixed $artifact, string $label): void
    {
        if (!is_array($artifact) || !is_string($artifact['url'] ?? null)
            || !is_int($artifact['size'] ?? null) || $artifact['size'] < 1
            || $artifact['size'] > $this->config->maxArchiveBytes
            || !preg_match('/^[a-f0-9]{64}$/', (string) ($artifact['sha256'] ?? ''))) {
            throw new RuntimeException("{$label}の情報が不正です。");
        }
        $this->assertAllowedUrl($artifact['url']);
    }

    private function validateFreshness(mixed $freshness): void
    {
        $public = is_array($freshness) ? base64_decode((string) ($freshness['public_key'] ?? ''), true) : false;
        if (!is_array($freshness) || !is_string($public)
            || strlen($public) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
            || !hash_equals(hash('sha256', $public), (string) ($freshness['keyid'] ?? ''))
            || !is_int($freshness['sequence_floor'] ?? null) || $freshness['sequence_floor'] < 1
            || !is_int($freshness['max_lease_seconds'] ?? null)
            || $freshness['max_lease_seconds'] < 3600 || $freshness['max_lease_seconds'] > 7_776_000) {
            throw new RuntimeException('更新情報の署名鍵設定が不正です。');
        }
    }

    /** @param array<string, mixed> $envelope @param array<string, mixed> $signed @return array{int, string} */
    private function verifyLease(
        array $envelope,
        array $signed,
        string $rootDigest,
        int $highestSequence,
        ?string $highestDigest,
    ): array {
        $lease = $envelope['lease'] ?? null;
        $leaseSigned = is_array($lease) ? ($lease['signed'] ?? null) : null;
        $signatures = is_array($lease) ? ($lease['signatures'] ?? null) : null;
        if (!is_array($leaseSigned) || !is_array($signatures) || !array_is_list($signatures)) {
            throw new RuntimeException('更新情報の有効期限署名がありません。');
        }
        $freshness = $signed['freshness'];
        $public = base64_decode((string) $freshness['public_key'], true);
        $leasePayload = CanonicalJson::encode($leaseSigned);
        if ($this->verifySignatures($leasePayload, $signatures, [$freshness['keyid'] => $public]) === null) {
            throw new RuntimeException('更新情報の有効期限署名を検証できません。');
        }
        $required = [
            'schema', 'manifest_sha256', 'target_version', 'artifact_sha256', 'full_artifact_sha256',
            'from_versions_sha256', 'migrations_sha256', 'sequence', 'issued_at', 'expires_at',
        ];
        foreach ($required as $key) {
            if (!array_key_exists($key, $leaseSigned)) {
                throw new RuntimeException("更新情報の有効期限署名に {$key} がありません。");
            }
        }
        $bindings = [
            'manifest_sha256' => $rootDigest,
            'target_version' => $signed['target_version'],
            'artifact_sha256' => $signed['artifact']['sha256'],
            'full_artifact_sha256' => $signed['full_artifact']['sha256'],
            'from_versions_sha256' => hash('sha256', CanonicalJson::encode($signed['from_versions'])),
            'migrations_sha256' => hash('sha256', CanonicalJson::encode($signed['migrations'])),
        ];
        foreach ($bindings as $key => $expected) {
            if (!is_string($leaseSigned[$key]) || !hash_equals((string) $expected, $leaseSigned[$key])) {
                throw new RuntimeException('更新情報の有効期限署名が配布内容と一致しません。');
            }
        }
        $sequence = $leaseSigned['sequence'];
        if ($leaseSigned['schema'] !== 1 || !is_int($sequence)
            || $sequence < (int) $freshness['sequence_floor'] || $sequence < $highestSequence) {
            throw new RuntimeException('過去の更新情報は使用できません。');
        }
        $digest = hash('sha256', $leasePayload);
        if ($sequence === $highestSequence && $highestDigest !== null && !hash_equals($highestDigest, $digest)) {
            throw new RuntimeException('同じ連番で内容が異なる更新情報は使用できません。');
        }
        try {
            $issued = new DateTimeImmutable((string) $leaseSigned['issued_at']);
            $expires = new DateTimeImmutable((string) $leaseSigned['expires_at']);
        } catch (\Throwable) {
            throw new RuntimeException('更新情報の有効期限が不正です。');
        }
        $now = new DateTimeImmutable('now');
        if ($issued > $now->modify('+5 minutes') || $expires <= $now || $expires <= $issued
            || $expires->getTimestamp() - $issued->getTimestamp() > (int) $freshness['max_lease_seconds']) {
            throw new RuntimeException('更新情報の有効期限が不正です。');
        }
        return [$sequence, $digest];
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
