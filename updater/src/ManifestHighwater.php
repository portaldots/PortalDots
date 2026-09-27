<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class ManifestHighwater
{
    public function __construct(private readonly Config $config)
    {
    }

    /** @return array<string, mixed> */
    public function read(): array
    {
        $path = $this->config->privatePath . '/highest-manifest.json';
        if (!is_file($path)) {
            return [];
        }
        $contents = file_get_contents($path);
        $value = is_string($contents)
            ? json_decode($contents, true, 32, JSON_THROW_ON_ERROR)
            : null;
        if (!is_array($value) || !is_int($value['sequence'] ?? null) || $value['sequence'] < 1
            || !is_string($value['digest'] ?? null)
            || !preg_match('/^[a-f0-9]{64}$/', $value['digest'])) {
            throw new RuntimeException('保存された更新連番が破損しています。');
        }
        if (array_key_exists('lease_sequence', $value)
            && ($value['lease_sequence'] !== null && (!is_int($value['lease_sequence'])
                || $value['lease_sequence'] < 1
                || !is_string($value['lease_digest'] ?? null)
                || !preg_match('/^[a-f0-9]{64}$/', $value['lease_digest'])))) {
            throw new RuntimeException('保存された更新情報の連番が破損しています。');
        }
        return $value;
    }

    /** @param array<string, mixed> $verified */
    public function observe(array $verified): void
    {
        $lock = fopen($this->config->privatePath . '/highest-manifest.lock', 'c+');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            if (is_resource($lock)) {
                fclose($lock);
            }
            throw new RuntimeException('更新連番の確認を開始できません。');
        }
        try {
            $highest = $this->read();
            $this->assertNotRolledBack($verified, $highest);
            $this->write([
                'sequence' => $verified['signed']['sequence'],
                'digest' => $verified['root_digest'],
                'lease_sequence' => $verified['lease_sequence'],
                'lease_digest' => $verified['lease_digest'],
                'target_version' => $verified['signed']['target_version'],
            ]);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /** @param array<string, mixed> $verified @param array<string, mixed> $highest */
    private function assertNotRolledBack(array $verified, array $highest): void
    {
        $sequence = (int) $verified['signed']['sequence'];
        $highestSequence = (int) ($highest['sequence'] ?? 0);
        if ($sequence < $highestSequence) {
            throw new RuntimeException('過去の更新マニフェストは使用できません。');
        }
        if ($sequence === $highestSequence && isset($highest['digest'])
            && !hash_equals((string) $highest['digest'], (string) $verified['root_digest'])) {
            throw new RuntimeException('同じ連番で内容が異なる更新マニフェストは使用できません。');
        }

        $leaseSequence = $verified['lease_sequence'];
        if ($leaseSequence === null || $sequence !== $highestSequence) {
            return;
        }
        $highestLease = (int) ($highest['lease_sequence'] ?? 0);
        if ($leaseSequence < $highestLease) {
            throw new RuntimeException('過去の更新情報は使用できません。');
        }
        if ($leaseSequence === $highestLease && isset($highest['lease_digest'])
            && !hash_equals((string) $highest['lease_digest'], (string) $verified['lease_digest'])) {
            throw new RuntimeException('同じ連番で内容が異なる更新情報は使用できません。');
        }
    }

    /** @param array<string, mixed> $value */
    private function write(array $value): void
    {
        $path = $this->config->privatePath . '/highest-manifest.json';
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $contents = CanonicalJson::encode($value) . "\n";
        $handle = fopen($temporary, 'xb');
        if ($handle === false || fwrite($handle, $contents) !== strlen($contents)
            || !fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            @unlink($temporary);
            throw new RuntimeException('更新連番を保存できません。');
        }
        fclose($handle);
        @chmod($temporary, 0600);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('更新連番を確定できません。');
        }
    }
}
