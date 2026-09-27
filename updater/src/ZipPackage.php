<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;
use ZipArchive;

final class ZipPackage
{
    private const PROTECTED_PREFIXES = [
        '.env', 'storage/', 'updater/', 'bootstrap/cache/', 'public/updater.php',
        'public/storage/', 'public/uploads/',
    ];

    public function __construct(private readonly Config $config)
    {
    }

    /** @param list<array{path: string, sha256: string, size: int}> $expected */
    public function verifyAndExtract(string $archive, string $destination, array $expected): void
    {
        if (!extension_loaded('zip')) {
            throw new RuntimeException('更新展開に必要な ZIP 拡張がありません。');
        }
        $zip = new ZipArchive();
        if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('更新ZIPを開けません。');
        }
        try {
            if ($zip->numFiles > $this->config->maxArchiveFiles) {
                throw new RuntimeException('更新ZIPのファイル数が上限を超えています。');
            }
            $expectedByPath = [];
            foreach ($expected as $file) {
                $expectedByPath[$file['path']] = $file;
            }
            $seen = [];
            $total = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index, ZipArchive::FL_UNCHANGED);
                if (!is_array($stat)) {
                    throw new RuntimeException('更新ZIPの索引を読み取れません。');
                }
                $raw = (string) $stat['name'];
                $path = self::normalizePath($raw);
                if ($path !== $raw || str_ends_with($path, '/')) {
                    throw new RuntimeException("更新ZIPに不正な項目があります: {$raw}");
                }
                $lower = strtolower($path);
                if (isset($seen[$lower]) || !isset($expectedByPath[$path])) {
                    throw new RuntimeException("更新ZIPに重複または未宣言のファイルがあります: {$path}");
                }
                $seen[$lower] = true;
                $size = (int) ($stat['size'] ?? -1);
                $compressed = (int) ($stat['comp_size'] ?? 0);
                if ($size < 0 || $size !== $expectedByPath[$path]['size']) {
                    throw new RuntimeException("更新ZIPのファイルサイズが一致しません: {$path}");
                }
                $total += $size;
                if ($total > $this->config->maxExtractedBytes
                    || ($size > 10_485_760 && $compressed > 0 && $size / $compressed > 200)) {
                    throw new RuntimeException('更新ZIPの展開容量または圧縮率が上限を超えています。');
                }
                $opsys = 0;
                $attributes = 0;
                if ($zip->getExternalAttributesIndex($index, $opsys, $attributes)) {
                    $mode = ($attributes >> 16) & 0170000;
                    if ($mode === 0120000) {
                        throw new RuntimeException("更新ZIPにシンボリックリンクがあります: {$path}");
                    }
                }
            }
            if (count($seen) !== count($expectedByPath)) {
                throw new RuntimeException('更新ZIPに不足しているファイルがあります。');
            }

            if (!is_dir($destination) && !mkdir($destination, 0700, true) && !is_dir($destination)) {
                throw new RuntimeException('更新ZIPの展開先を作成できません。');
            }
            foreach (array_keys($expectedByPath) as $path) {
                $target = $destination . '/' . $path;
                $directory = dirname($target);
                if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
                    throw new RuntimeException("更新ファイルの展開先を作成できません: {$path}");
                }
                $input = $zip->getStream($path);
                $output = fopen($target, 'xb');
                if ($input === false || $output === false) {
                    if (is_resource($input)) {
                        fclose($input);
                    }
                    throw new RuntimeException("更新ファイルを展開できません: {$path}");
                }
                stream_copy_to_stream($input, $output);
                fclose($input);
                fflush($output);
                fclose($output);
                @chmod($target, 0600);
                $actualHash = hash_file('sha256', $target);
                if (!is_string($actualHash)
                    || !hash_equals($expectedByPath[$path]['sha256'], $actualHash)) {
                    throw new RuntimeException("更新ファイルのハッシュが一致しません: {$path}");
                }
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Inspect the whole central directory without writing files.
     *
     * @param list<array{path: string, sha256: string, size: int}> $expected
     */
    public function inspect(string $archive, array $expected): void
    {
        $zip = new ZipArchive();
        if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('更新ZIPを開けません。');
        }
        try {
            if ($zip->numFiles > $this->config->maxArchiveFiles) {
                throw new RuntimeException('更新ZIPのファイル数が上限を超えています。');
            }
            $expectedByPath = array_column($expected, null, 'path');
            $seen = [];
            $total = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index, ZipArchive::FL_UNCHANGED);
                if (!is_array($stat)) {
                    throw new RuntimeException('更新ZIPの索引を読み取れません。');
                }
                $raw = (string) $stat['name'];
                $path = self::normalizePath($raw);
                $lower = strtolower($path);
                if ($path !== $raw || str_ends_with($path, '/') || isset($seen[$lower])
                    || !isset($expectedByPath[$path])) {
                    throw new RuntimeException("更新ZIPに不正・重複・未宣言の項目があります: {$raw}");
                }
                $size = (int) ($stat['size'] ?? -1);
                $compressed = (int) ($stat['comp_size'] ?? 0);
                if ($size < 0 || $size !== $expectedByPath[$path]['size']) {
                    throw new RuntimeException("更新ZIPのファイルサイズが一致しません: {$path}");
                }
                $total += $size;
                if ($total > $this->config->maxExtractedBytes
                    || ($size > 10_485_760 && $compressed > 0 && $size / $compressed > 200)) {
                    throw new RuntimeException('更新ZIPの展開容量または圧縮率が上限を超えています。');
                }
                $opsys = 0;
                $attributes = 0;
                if ($zip->getExternalAttributesIndex($index, $opsys, $attributes)
                    && (($attributes >> 16) & 0170000) === 0120000) {
                    throw new RuntimeException("更新ZIPにシンボリックリンクがあります: {$path}");
                }
                $seen[$lower] = true;
            }
            if (count($seen) !== count($expectedByPath)) {
                throw new RuntimeException('更新ZIPに不足しているファイルがあります。');
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Extract and hash one file. Returns true when all files are extracted.
     *
     * @param list<array{path: string, sha256: string, size: int}> $expected
     * @param array<string, mixed> $context
     */
    public function extractStep(string $archive, string $destination, array $expected, array &$context): bool
    {
        $index = (int) ($context['index'] ?? 0);
        if ($index >= count($expected)) {
            return true;
        }
        $file = $expected[$index];
        $zip = new ZipArchive();
        if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('更新ZIPを開けません。');
        }
        try {
            return $this->extractOpened($zip, $destination, $expected, $context);
        } finally {
            $zip->close();
        }
    }

    /**
     * Extract as many complete files as fit in one short request while keeping the central directory open.
     *
     * @param list<array{path: string, sha256: string, size: int}> $expected
     * @param array<string, mixed> $context
     */
    public function extractUntil(
        string $archive,
        string $destination,
        array $expected,
        array &$context,
        float $deadline,
    ): bool {
        $zip = new ZipArchive();
        if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('更新ZIPを開けません。');
        }
        try {
            do {
                $complete = $this->extractOpened($zip, $destination, $expected, $context);
            } while (!$complete && microtime(true) < $deadline);
            return $complete;
        } finally {
            $zip->close();
        }
    }

    /** @param list<array{path: string, sha256: string, size: int}> $expected @param array<string, mixed> $context */
    private function extractOpened(ZipArchive $zip, string $destination, array $expected, array &$context): bool
    {
        $index = (int) ($context['index'] ?? 0);
        if ($index >= count($expected)) {
            return true;
        }
        $file = $expected[$index];
        $target = $destination . '/' . $file['path'];
        $directory = dirname($target);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException("更新ファイルの展開先を作成できません: {$file['path']}");
        }
        if (!is_file($target)) {
            $partial = $target . '.part';
            if (is_file($partial) && !unlink($partial)) {
                throw new RuntimeException("中断した更新ファイルを再開できません: {$file['path']}");
            }
            $input = $zip->getStream($file['path']);
            $output = fopen($partial, 'xb');
            if ($input === false || $output === false) {
                if (is_resource($input)) {
                    fclose($input);
                }
                throw new RuntimeException("更新ファイルを展開できません: {$file['path']}");
            }
            $bytes = stream_copy_to_stream($input, $output, $file['size'] + 1);
            fclose($input);
            $durable = fflush($output) && (!function_exists('fsync') || fsync($output));
            fclose($output);
            if ($bytes !== $file['size'] || !$durable) {
                @unlink($partial);
                throw new RuntimeException("更新ファイルの展開サイズが一致しません: {$file['path']}");
            }
            $hash = hash_file('sha256', $partial);
            if (!is_string($hash) || !hash_equals($file['sha256'], $hash) || !rename($partial, $target)) {
                @unlink($partial);
                throw new RuntimeException("更新ファイルのハッシュが一致しません: {$file['path']}");
            }
            @chmod($target, 0600);
        }
        $existingHash = hash_file('sha256', $target);
        if (!is_string($existingHash) || !hash_equals($file['sha256'], $existingHash)
            || filesize($target) !== $file['size']) {
            throw new RuntimeException("展開済み更新ファイルを検証できません: {$file['path']}");
        }
        $context['index'] = $index + 1;
        return $context['index'] >= count($expected);
    }

    public static function normalizePath(string $path): string
    {
        if ($path === '' || str_contains($path, "\0") || str_contains($path, '\\')
            || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:/', $path)) {
            throw new RuntimeException('絶対パスまたは不正なパスは使用できません。');
        }
        $segments = explode('/', $path);
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new RuntimeException('パストラバーサルを検出しました。');
            }
        }
        return implode('/', $segments);
    }

    public static function isProtectedPath(string $path): bool
    {
        foreach (self::PROTECTED_PREFIXES as $prefix) {
            if ($path === rtrim($prefix, '/') || (str_ends_with($prefix, '/') && str_starts_with($path, $prefix))) {
                return true;
            }
        }
        return false;
    }
}
