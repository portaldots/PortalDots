<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class TrustedKeyStore
{
    public function __construct(
        private readonly Config $config,
        private readonly StateStore $store,
    ) {
    }

    /** @return array<string, string> key ID => binary public key */
    public function all(int $sequence = PHP_INT_MAX): array
    {
        $keys = [];
        $path = $this->config->privatePath . '/trusted-keys.json';
        $decodedStore = is_file($path)
            ? json_decode((string) file_get_contents($path), true, 32, JSON_THROW_ON_ERROR)
            : ['keys' => [], 'retirements' => []];
        $retirements = is_array($decodedStore['retirements'] ?? null) ? $decodedStore['retirements'] : [];
        $bundled = is_file($this->config->publicKeyPath)
            ? trim((string) file_get_contents($this->config->publicKeyPath)) : '';
        if ($bundled !== '' && $bundled !== 'UNCONFIGURED') {
            $decoded = base64_decode($bundled, true);
            if (!is_string($decoded) || strlen($decoded) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
                throw new RuntimeException('同梱された更新署名公開鍵が不正です。');
            }
            $keyId = hash('sha256', $decoded);
            if ($sequence < (int) ($retirements[$keyId] ?? PHP_INT_MAX)) {
                $keys[$keyId] = $decoded;
            }
        }

        if (is_file($path)) {
            foreach (($decodedStore['keys'] ?? []) as $keyId => $entry) {
                $encoded = is_array($entry) ? ($entry['public_key'] ?? null) : $entry;
                $activation = is_array($entry) ? (int) ($entry['activate_at_sequence'] ?? PHP_INT_MAX) : 0;
                $key = is_string($encoded) ? base64_decode($encoded, true) : false;
                if (is_string($key) && strlen($key) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES
                    && $sequence >= $activation
                    && $sequence < (int) ($retirements[$keyId] ?? PHP_INT_MAX)
                    && hash_equals((string) $keyId, hash('sha256', $key))) {
                    $keys[(string) $keyId] = $key;
                }
            }
        }
        return $keys;
    }

    /** @param array<string, mixed> $signed */
    public function applyRotation(array $signed, string $signingKeyId): void
    {
        $rotations = $signed['next_keys'] ?? [];
        $retired = $signed['retired_keys'] ?? [];
        if (!is_array($rotations) || !is_array($retired) || ($rotations === [] && $retired === [])) {
            return;
        }
        $keys = $this->all((int) ($signed['sequence'] ?? 0));
        if (!isset($keys[$signingKeyId])) {
            throw new RuntimeException('鍵更新を承認した署名鍵が信頼されていません。');
        }
        $sequence = (int) ($signed['sequence'] ?? 0);
        foreach ($rotations as $rotation) {
            if (!is_array($rotation) || (int) ($rotation['activate_after_sequence'] ?? 0) <= $sequence) {
                throw new RuntimeException('署名鍵の更新条件が不正です。');
            }
            $key = base64_decode((string) ($rotation['public_key'] ?? ''), true);
            if (!is_string($key) || strlen($key) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
                throw new RuntimeException('更新先の署名公開鍵が不正です。');
            }
            $keys[hash('sha256', $key)] = $key;
        }

        $path = $this->config->privatePath . '/trusted-keys.json';
        $existing = is_file($path)
            ? json_decode((string) file_get_contents($path), true, 32, JSON_THROW_ON_ERROR)
            : ['keys' => [], 'retirements' => []];
        $encoded = is_array($existing['keys'] ?? null) ? $existing['keys'] : [];
        $retirements = is_array($existing['retirements'] ?? null) ? $existing['retirements'] : [];
        foreach ($rotations as $rotation) {
            $key = base64_decode((string) $rotation['public_key'], true);
            $encoded[hash('sha256', $key)] = [
                'public_key' => base64_encode($key),
                'activate_at_sequence' => (int) $rotation['activate_after_sequence'],
            ];
        }
        foreach ($retired as $retirement) {
            if (!is_array($retirement)
                || !preg_match('/^[a-f0-9]{64}$/', (string) ($retirement['keyid'] ?? ''))
                || (int) ($retirement['retire_at_sequence'] ?? 0) <= $sequence
                || !isset($keys[$retirement['keyid']])) {
                throw new RuntimeException('署名鍵の失効条件が不正です。');
            }
            $retirements[$retirement['keyid']] = (int) $retirement['retire_at_sequence'];
        }
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        if (file_put_contents($temporary, CanonicalJson::encode([
            'keys' => $encoded,
            'retirements' => $retirements,
        ]) . "\n", LOCK_EX) === false) {
            throw new RuntimeException('信頼済み署名鍵を保存できません。');
        }
        @chmod($temporary, 0600);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('信頼済み署名鍵を確定できません。');
        }
    }
}
