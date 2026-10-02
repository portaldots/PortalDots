<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class UpdateGate
{
    /** @var resource|null */
    private static $lease = null;

    public static function enter(string $basePath): bool
    {
        try {
            $config = Config::fromEnvironment($basePath);
            PrivateStorage::ensure($config->privatePath);
            $privatePath = $config->privatePath;
        } catch (\Throwable) {
            return false;
        }
        if (is_file($privatePath . '/maintenance.json')) {
            return false;
        }
        $lockPath = $privatePath . '/traffic.lock';
        $handle = @fopen($lockPath, 'c+');
        if ($handle === false) {
            return false;
        }
        @chmod($lockPath, 0600);
        if (!@flock($handle, LOCK_SH)) {
            fclose($handle);
            return false;
        }

        if (is_file($privatePath . '/maintenance.json')) {
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

    public static function beginMaintenance(Config $config, string $jobId): void
    {
        $path = $config->privatePath . '/maintenance.json';
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $written = file_put_contents($temporary, CanonicalJson::encode([
            'job_id' => $jobId,
            'started_at' => gmdate(DATE_ATOM),
        ]) . "\n", LOCK_EX);
        if ($written === false) {
            throw new RuntimeException('メンテナンス状態を書き込めません。');
        }
        @chmod($temporary, 0600);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('メンテナンス状態を確定できません。');
        }
    }

    public static function waitForDrain(Config $config): bool
    {
        $handle = fopen($config->privatePath . '/traffic.lock', 'c+');
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
        $path = $config->privatePath . '/maintenance.json';
        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('メンテナンス状態を解除できません。');
        }
    }
}
