<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class PrivateStorage
{
    public static function ensure(string $path): void
    {
        if (!self::isAbsolute($path)) {
            throw new RuntimeException('更新用非公開領域は絶対パスで指定してください。');
        }
        if (!is_dir($path) && !@mkdir($path, 0700, true) && !is_dir($path)) {
            throw new RuntimeException('更新用非公開領域を作成できません。');
        }
        if (!@chmod($path, 0700) || !is_writable($path)) {
            throw new RuntimeException('更新用非公開領域の権限を設定できません。');
        }

        self::writeIfDifferent($path . '/.htaccess', implode("\n", [
            '# PortalDots updater backups must never be served over HTTP.',
            '<IfModule mod_authz_core.c>',
            '    Require all denied',
            '</IfModule>',
            '<IfModule !mod_authz_core.c>',
            '    Order Allow,Deny',
            '    Deny from all',
            '</IfModule>',
            '',
        ]));
        self::writeIfDifferent($path . '/index.html', "<!doctype html><title>Not Found</title>\n");
    }

    private static function writeIfDifferent(string $path, string $contents): void
    {
        if (is_file($path) && hash_equals(hash('sha256', $contents), (string) hash_file('sha256', $path))) {
            @chmod($path, 0600);
            return;
        }
        if (file_exists($path) && !is_file($path)) {
            throw new RuntimeException('更新用非公開領域の保護ファイルが不正です。');
        }
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $handle = @fopen($temporary, 'xb');
        if ($handle === false || fwrite($handle, $contents) !== strlen($contents)
            || !fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            @unlink($temporary);
            throw new RuntimeException('更新用非公開領域の保護設定を書き込めません。');
        }
        fclose($handle);
        @chmod($temporary, 0600);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('更新用非公開領域の保護設定を確定できません。');
        }
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }
}
