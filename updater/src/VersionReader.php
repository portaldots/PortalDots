<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class VersionReader
{
    public static function current(string $basePath): string
    {
        $path = rtrim($basePath, DIRECTORY_SEPARATOR) . '/app/ReleaseInfo.php';
        $contents = is_file($path) ? file_get_contents($path) : false;
        if (!is_string($contents)
            || !preg_match("/public const VERSION = '([^']+)'/", $contents, $matches)
            || !preg_match('/^(0|[1-9]\\d*)\\.(0|[1-9]\\d*)\\.(0|[1-9]\\d*)$/', $matches[1])) {
            throw new RuntimeException('現在のPortalDotsバージョンを確認できません。開発版は更新できません。');
        }
        return $matches[1];
    }
}
