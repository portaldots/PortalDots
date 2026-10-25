<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class UpdateGate
{
    // 停止の目印と通信ロックは、Laravel が Web と CLI の両方に書き込みを求める
    // storage/framework に置く。実行ユーザーが異なっても、インストール時の権限設定のまま共有できる。
    private const MAINTENANCE_FILE = 'portaldots-update-maintenance.json';
    private const TRAFFIC_LOCK = 'portaldots-update-traffic.lock';

    /** @var resource|null */
    private static $lease = null;

    public static function enter(string $basePath): bool
    {
        $maintenancePath = self::path($basePath, self::MAINTENANCE_FILE);
        if (is_file($maintenancePath)) {
            return false;
        }
        $lockPath = self::path($basePath, self::TRAFFIC_LOCK);
        $handle = @fopen($lockPath, 'c+');
        if ($handle === false) {
            $handle = @fopen($lockPath, 'r');
        }
        if ($handle === false) {
            return false;
        }
        if (!@flock($handle, LOCK_SH)) {
            fclose($handle);
            return false;
        }

        if (is_file($maintenancePath)) {
            flock($handle, LOCK_UN);
            fclose($handle);
            return false;
        }

        self::$lease = $handle;
        register_shutdown_function(static function (): void {
            if (is_resource(self::$lease)) {
                flock(self::$lease, LOCK_UN);
                fclose(self::$lease);
                self::$lease = null;
            }
        });
        return true;
    }

    public static function isInMaintenance(Config $config): bool
    {
        return is_file(self::path($config->basePath, self::MAINTENANCE_FILE));
    }

    public static function beginMaintenance(Config $config, string $jobId): void
    {
        $path = self::path($config->basePath, self::MAINTENANCE_FILE);
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $written = file_put_contents($temporary, CanonicalJson::encode([
            'job_id' => $jobId,
            'started_at' => gmdate(DATE_ATOM),
        ]) . "\n", LOCK_EX);
        if ($written === false) {
            throw new RuntimeException('メンテナンス状態を書き込めません。');
        }
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('メンテナンス状態を確定できません。');
        }
    }

    public static function waitForDrain(Config $config): bool
    {
        // CLI が先に作ったロックは Web の実行ユーザーから書き込めない場合がある。
        // 排他ロックは読み取り専用で開いた記述子でも取れる。
        $lockPath = self::path($config->basePath, self::TRAFFIC_LOCK);
        $handle = @fopen($lockPath, 'c+');
        if ($handle === false) {
            $handle = @fopen($lockPath, 'r');
        }
        if ($handle === false) {
            throw new RuntimeException('通信停止ロックを開けません。');
        }
        $acquired = flock($handle, LOCK_EX | LOCK_NB);
        if ($acquired) {
            flock($handle, LOCK_UN);
        }
        fclose($handle);
        return $acquired;
    }

    public static function endMaintenance(Config $config): void
    {
        $path = self::path($config->basePath, self::MAINTENANCE_FILE);
        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('メンテナンス状態を解除できません。');
        }
    }

    private static function path(string $basePath, string $name): string
    {
        return rtrim($basePath, DIRECTORY_SEPARATOR) . '/storage/framework/' . $name;
    }
}
