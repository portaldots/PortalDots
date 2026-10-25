<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class CacheCleaner
{
    private const FILES = [
        'bootstrap/cache/config.php',
        'bootstrap/cache/events.php',
        'bootstrap/cache/packages.php',
        'bootstrap/cache/routes-v7.php',
        'bootstrap/cache/services.php',
    ];

    public static function clear(string $basePath): void
    {
        foreach (self::FILES as $relative) {
            $path = rtrim($basePath, DIRECTORY_SEPARATOR) . '/' . $relative;
            if (function_exists('opcache_is_script_cached') && opcache_is_script_cached($path)
                && (!function_exists('opcache_invalidate') || !opcache_invalidate($path, true))) {
                throw new RuntimeException("起動キャッシュのOPcacheを無効化できません: {$relative}");
            }
            if (is_file($path) && !unlink($path)) {
                throw new RuntimeException("起動キャッシュを削除できません: {$relative}");
            }
        }
    }
}
