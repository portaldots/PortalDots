<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class ManifestHighwater
{
    public function __construct(private readonly Config $config)
    {
    }

    /** @return array<string, mixed> 対象メジャーバージョンの記録。無ければ空配列。 */
    public function read(int $major): array
    {
        return $this->readAll()[(string) $major] ?? [];
    }

    /**
     * 保存内容をメジャーバージョンごとの記録に読み替える。
     * リリースworkflowの実行番号をそのままsequenceに使うため、旧メジャーのパッチが後から出ると
     * 新メジャーより高いsequenceを持ちうる。単一のグローバル最高値で比較すると、新メジャーへの更新が
     * 一時的なPHP・MySQL要件不足でメジャー内フォールバックへ切り替わった後、要件を満たしても
     * 「過去の更新マニフェスト」として弾かれてしまうため、メジャーごとに最高値を分離して保持する。
     *
     * @return array<string, array<string, mixed>> メジャーバージョン文字列をキーとする記録
     */
    private function readAll(): array
    {
        $path = $this->config->privatePath . '/highest-manifest.json';
        if (!is_file($path)) {
            return [];
        }
        $contents = file_get_contents($path);
        $value = is_string($contents)
            ? json_decode($contents, true, 32, JSON_THROW_ON_ERROR)
            : null;
        if (!is_array($value)) {
            throw new RuntimeException('保存された更新連番が破損しています。');
        }
        if (array_key_exists('majors', $value)) {
            if (!is_array($value['majors'])) {
                throw new RuntimeException('保存された更新連番が破損しています。');
            }
            foreach ($value['majors'] as $major => $record) {
                // JSONオブジェクトのキーが数字だけの文字列だと、json_decode(...,true)が
                // 連想配列のキーを自動的にintへ変換するため、文字列型であることは要求しない。
                if (!preg_match('/^(0|[1-9]\d*)$/', (string) $major)) {
                    throw new RuntimeException('保存された更新連番が破損しています。');
                }
                $this->assertValidRecord($record);
            }
            return $value['majors'];
        }
        // メジャー別に分かれていない旧形式は、その記録のtarget_versionが属するメジャーの
        // 記録として読み替える。
        $this->assertValidRecord($value);
        $major = explode('.', (string) $value['target_version'], 2)[0];
        return [$major => $value];
    }

    /** @param mixed $record */
    private function assertValidRecord($record): void
    {
        if (!is_array($record) || !is_int($record['sequence'] ?? null) || $record['sequence'] < 1
            || !is_string($record['digest'] ?? null)
            || !preg_match('/^[a-f0-9]{64}$/', $record['digest'])
            || !is_string($record['target_version'] ?? null)) {
            throw new RuntimeException('保存された更新連番が破損しています。');
        }
        if (array_key_exists('lease_sequence', $record)
            && ($record['lease_sequence'] !== null && (!is_int($record['lease_sequence'])
                || $record['lease_sequence'] < 1
                || !is_string($record['lease_digest'] ?? null)
                || !preg_match('/^[a-f0-9]{64}$/', $record['lease_digest'])))) {
            throw new RuntimeException('保存された更新情報の連番が破損しています。');
        }
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
            $majors = $this->readAll();
            $target = (string) $verified['signed']['target_version'];
            $major = explode('.', $target, 2)[0];
            $this->assertNotRolledBack($verified, $majors[$major] ?? []);
            $majors[$major] = [
                'sequence' => $verified['signed']['sequence'],
                'digest' => $verified['root_digest'],
                'lease_sequence' => $verified['lease_sequence'],
                'lease_digest' => $verified['lease_digest'],
                'target_version' => $target,
            ];
            $this->write(['majors' => $majors]);
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
