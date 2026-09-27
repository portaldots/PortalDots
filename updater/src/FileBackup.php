<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class FileBackup
{
    public function __construct(private readonly Config $config)
    {
    }

    /** @return array<string, mixed> */
    public function installedManifest(string $currentVersion): array
    {
        $path = $this->config->basePath . '/.portaldots-manifest.json';
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('公式配布ファイル一覧がありません。最初に手動更新が必要です。');
        }
        $manifest = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($manifest) || ($manifest['schema'] ?? null) !== 1
            || ($manifest['version'] ?? null) !== $currentVersion || !is_array($manifest['files'] ?? null)) {
            throw new RuntimeException('公式配布ファイル一覧が現在版と一致しません。');
        }
        return $manifest;
    }

    /**
     * Verify every officially distributed file before maintenance.
     *
     * @param array<string, mixed> $manifest
     */
    public function verifyInstallation(array $manifest): void
    {
        $seen = [];
        foreach ($manifest['files'] as $file) {
            if (!is_array($file) || !is_string($file['path'] ?? null)
                || !preg_match('/^[a-f0-9]{64}$/', (string) ($file['sha256'] ?? ''))) {
                throw new RuntimeException('公式配布ファイル一覧が不正です。');
            }
            $path = ZipPackage::normalizePath($file['path']);
            if (isset($seen[strtolower($path)])) {
                throw new RuntimeException('公式配布ファイル一覧に重複があります。');
            }
            $seen[strtolower($path)] = true;
            $absolute = $this->config->basePath . '/' . $path;
            $this->assertSafePath($this->config->basePath, $path);
            if (!is_file($absolute) || is_link($absolute)) {
                throw new RuntimeException("公式配布ファイルが欠損またはリンク化されています: {$path}");
            }
            $actual = hash_file('sha256', $absolute);
            if (!is_string($actual) || !hash_equals($file['sha256'], $actual)) {
                throw new RuntimeException("公式配布ファイルが変更されています: {$path}");
            }
        }
        $this->rejectUnknownManagedFiles($seen);
    }

    /**
     * @param array<string, mixed> $installed
     * @param list<array{path: string, sha256: string, size: int}> $incoming
     * @return array<string, mixed>
     */
    public function plan(array $installed, array $incoming, string $targetVersion, int $sequence): array
    {
        $old = array_column($installed['files'], null, 'path');
        $new = array_column($incoming, null, 'path');
        $backup = [];
        $apply = [];
        $delete = [];
        $removeOnRestore = [];
        foreach ($new as $path => $file) {
            if (!isset($old[$path])) {
                $absolute = $this->config->basePath . '/' . $path;
                if (file_exists($absolute) || is_link($absolute)) {
                    throw new RuntimeException("未管理ファイルと更新ファイルが衝突します: {$path}");
                }
                $this->assertSafePath($this->config->basePath, $path, true);
                $apply[] = $path;
                $removeOnRestore[] = $path;
            } elseif (!hash_equals((string) $old[$path]['sha256'], (string) $file['sha256'])) {
                $backup[] = $path;
                $apply[] = $path;
            }
        }
        foreach ($old as $path => $file) {
            if (!isset($new[$path]) && !ZipPackage::isProtectedPath($path)) {
                $backup[] = $path;
                $delete[] = $path;
            }
        }
        $protected = array_values(array_filter(
            $installed['files'],
            static fn (array $file): bool => ZipPackage::isProtectedPath((string) $file['path']),
        ));
        $resultFiles = array_values(array_merge($incoming, $protected));
        usort($resultFiles, static fn (array $a, array $b): int => strcmp($a['path'], $b['path']));
        return [
            'backup' => array_values(array_unique($backup)),
            'apply' => $apply,
            'delete' => $delete,
            'remove_on_restore' => $removeOnRestore,
            'old_modes' => array_reduce($backup, function (array $modes, string $path): array {
                $modes[$path] = fileperms($this->config->basePath . '/' . $path) & 0777;
                return $modes;
            }, []),
            'old_manifest' => $installed,
            'new_manifest' => [
                'schema' => 1,
                'version' => $targetVersion,
                'sequence' => $sequence,
                'files' => $resultFiles,
            ],
        ];
    }

    /** @param array<string, mixed> $plan @param array<string, mixed> $context */
    public function backupStep(array $plan, array &$context, string $jobPath): bool
    {
        $index = (int) ($context['index'] ?? 0);
        if ($index >= count($plan['backup'])) {
            $context['complete'] = true;
            return true;
        }
        $path = $plan['backup'][$index];
        $source = $this->config->basePath . '/' . $path;
        $this->assertSafePath($this->config->basePath, $path);
        $destination = $jobPath . '/files/' . $path;
        $directory = dirname($destination);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException("ファイルバックアップ先を作成できません: {$path}");
        }
        if (!is_file($destination)) {
            $temporary = $destination . '.tmp.' . bin2hex(random_bytes(4));
            $this->copyDurably($source, $temporary);
            if (!rename($temporary, $destination)) {
                @unlink($temporary);
                throw new RuntimeException("ファイルバックアップを確定できません: {$path}");
            }
            @chmod($destination, 0600);
        }
        $expected = array_column($plan['old_manifest']['files'], null, 'path')[$path]['sha256'];
        $actual = hash_file('sha256', $destination);
        if (!is_string($actual) || !hash_equals((string) $expected, $actual)) {
            throw new RuntimeException("ファイルバックアップを検証できません: {$path}");
        }
        $context['index'] = $index + 1;
        return $context['index'] >= count($plan['backup']);
    }

    /** @param array<string, mixed> $plan @param array<string, mixed> $context */
    public function applyStep(array $plan, array &$context, string $stagingPath): bool
    {
        $operations = [];
        foreach ($plan['apply'] as $path) {
            $operations[] = ['action' => 'apply', 'path' => $path];
        }
        foreach ($plan['delete'] as $path) {
            $operations[] = ['action' => 'delete', 'path' => $path];
        }
        $operations[] = ['action' => 'manifest', 'path' => '.portaldots-manifest.json'];
        $index = (int) ($context['index'] ?? 0);
        if ($index >= count($operations)) {
            return true;
        }
        $operation = $operations[$index];
        $target = $this->config->basePath . '/' . $operation['path'];
        if ($operation['action'] === 'apply') {
            $source = $stagingPath . '/' . $operation['path'];
            $this->assertSafePath($stagingPath, $operation['path']);
            $incoming = array_column($plan['new_manifest']['files'], null, 'path')[$operation['path']] ?? null;
            $sourceHash = hash_file('sha256', $source);
            if (!is_array($incoming) || !is_string($sourceHash)
                || !hash_equals((string) $incoming['sha256'], $sourceHash)) {
                throw new RuntimeException("適用直前の更新ファイル検証に失敗しました: {$operation['path']}");
            }
            $this->assertSafePath($this->config->basePath, $operation['path'], true);
            $directory = dirname($target);
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException("更新先ディレクトリを作成できません: {$operation['path']}");
            }
            $temporary = $target . '.pdu-new';
            @unlink($temporary);
            $this->copyDurably($source, $temporary);
            $mode = is_file($target) ? (fileperms($target) & 0777) : (int) ($incoming['mode'] ?? 0644);
            @chmod($temporary, $mode);
            if (!rename($temporary, $target)) {
                @unlink($temporary);
                throw new RuntimeException("更新ファイルを配置できません: {$operation['path']}");
            }
            if (str_ends_with($target, '.php') && function_exists('opcache_is_script_cached')
                && opcache_is_script_cached($target)
                && (!function_exists('opcache_invalidate') || !opcache_invalidate($target, true))) {
                throw new RuntimeException("更新ファイルのOPcacheを無効化できません: {$operation['path']}");
            }
        } elseif ($operation['action'] === 'delete') {
            if (str_ends_with($target, '.php') && function_exists('opcache_is_script_cached')
                && opcache_is_script_cached($target)
                && (!function_exists('opcache_invalidate') || !opcache_invalidate($target, true))) {
                throw new RuntimeException("削除ファイルのOPcacheを無効化できません: {$operation['path']}");
            }
            if ((is_file($target) || is_link($target)) && !unlink($target)) {
                throw new RuntimeException("旧ファイルを削除できません: {$operation['path']}");
            }
        } else {
            $this->writeManifest($target, $plan['new_manifest']);
        }
        $context['index'] = $index + 1;
        return $context['index'] >= count($operations);
    }

    /** @param array<string, mixed> $plan @param array<string, mixed> $context */
    public function restoreStep(array $plan, array &$context, string $jobPath): bool
    {
        $operations = [];
        foreach ($plan['remove_on_restore'] as $path) {
            $operations[] = ['action' => 'remove', 'path' => $path];
        }
        foreach ($plan['backup'] as $path) {
            $operations[] = ['action' => 'restore', 'path' => $path];
        }
        $operations[] = ['action' => 'manifest', 'path' => '.portaldots-manifest.json'];
        $index = (int) ($context['index'] ?? 0);
        if ($index >= count($operations)) {
            return true;
        }
        $operation = $operations[$index];
        $target = $this->config->basePath . '/' . $operation['path'];
        if ($operation['action'] === 'remove') {
            if ((is_file($target) || is_link($target)) && !unlink($target)) {
                throw new RuntimeException("追加ファイルを復元時に削除できません: {$operation['path']}");
            }
        } elseif ($operation['action'] === 'restore') {
            $source = $jobPath . '/files/' . $operation['path'];
            $this->assertSafePath($jobPath . '/files', $operation['path']);
            $this->assertSafePath($this->config->basePath, $operation['path'], true);
            $expected = array_column($plan['old_manifest']['files'], null, 'path')[$operation['path']]['sha256'];
            $hash = is_file($source) ? hash_file('sha256', $source) : false;
            if (!is_string($hash) || !hash_equals((string) $expected, $hash)) {
                throw new RuntimeException("復元元ファイルが破損しています: {$operation['path']}");
            }
            $directory = dirname($target);
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException("復元先ディレクトリを作成できません: {$operation['path']}");
            }
            $temporary = $target . '.pdu-restore';
            @unlink($temporary);
            $this->copyDurably($source, $temporary);
            if (!rename($temporary, $target)) {
                @unlink($temporary);
                throw new RuntimeException("旧ファイルを復元できません: {$operation['path']}");
            }
            @chmod($target, (int) ($plan['old_modes'][$operation['path']] ?? 0644));
            if (str_ends_with($target, '.php') && function_exists('opcache_is_script_cached')
                && opcache_is_script_cached($target)
                && (!function_exists('opcache_invalidate') || !opcache_invalidate($target, true))) {
                throw new RuntimeException("復元ファイルのOPcacheを無効化できません: {$operation['path']}");
            }
        } else {
            $this->writeManifest($target, $plan['old_manifest']);
        }
        $context['index'] = $index + 1;
        return $context['index'] >= count($operations);
    }

    /** @param array<string, mixed> $manifest */
    private function writeManifest(string $target, array $manifest): void
    {
        $temporary = $target . '.pdu-new';
        @unlink($temporary);
        $handle = fopen($temporary, 'xb');
        $contents = CanonicalJson::encode($manifest) . "\n";
        if ($handle === false || fwrite($handle, $contents) !== strlen($contents)
            || !fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            @unlink($temporary);
            throw new RuntimeException('公式配布ファイル一覧を書き込めません。');
        }
        fclose($handle);
        @chmod($temporary, 0644);
        if (!rename($temporary, $target)) {
            @unlink($temporary);
            throw new RuntimeException('公式配布ファイル一覧を確定できません。');
        }
    }

    private function copyDurably(string $source, string $destination): void
    {
        $input = fopen($source, 'rb');
        $output = fopen($destination, 'xb');
        if ($input === false || $output === false) {
            if (is_resource($input)) {
                fclose($input);
            }
            throw new RuntimeException('ファイルを安全にコピーできません。');
        }
        $copied = stream_copy_to_stream($input, $output);
        fclose($input);
        $durable = fflush($output) && (!function_exists('fsync') || fsync($output));
        fclose($output);
        $expectedSize = filesize($source);
        if ($copied === false || !is_int($expectedSize) || $copied !== $expectedSize || !$durable) {
            @unlink($destination);
            throw new RuntimeException('ファイルコピーを永続化できません。');
        }
    }

    private function assertSafePath(string $root, string $relative, bool $allowMissingLeaf = false): void
    {
        $relative = ZipPackage::normalizePath($relative);
        $rootReal = realpath($root);
        if ($rootReal === false) {
            throw new RuntimeException('ファイル操作の基準ディレクトリを確認できません。');
        }
        $current = $rootReal;
        $segments = explode('/', $relative);
        foreach ($segments as $index => $segment) {
            $current .= '/' . $segment;
            if (is_link($current)) {
                throw new RuntimeException("シンボリックリンクを経由するファイル操作を拒否しました: {$relative}");
            }
            if (file_exists($current)) {
                $resolved = realpath($current);
                if ($resolved === false || ($resolved !== $rootReal && !str_starts_with($resolved . '/', $rootReal . '/'))) {
                    throw new RuntimeException("配置外を指すファイルパスを拒否しました: {$relative}");
                }
            } elseif (!$allowMissingLeaf && $index === count($segments) - 1) {
                throw new RuntimeException("操作対象ファイルがありません: {$relative}");
            }
        }
    }

    /** @param array<string, bool> $expectedLowercase */
    private function rejectUnknownManagedFiles(array $expectedLowercase): void
    {
        $walk = function (string $directory, string $prefix = '') use (&$walk, $expectedLowercase): void {
            $entries = scandir($directory);
            if ($entries === false) {
                throw new RuntimeException('配布ファイルの追加改変を確認できません。');
            }
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $relative = $prefix === '' ? $entry : $prefix . '/' . $entry;
                if ($relative === '.portaldots-manifest.json' || $relative === '.env'
                    || $relative === 'storage' || str_starts_with($relative, 'storage/')
                    || $relative === 'bootstrap/cache' || str_starts_with($relative, 'bootstrap/cache/')
                    || $relative === 'public/storage' || str_starts_with($relative, 'public/storage/')
                    || $relative === 'public/uploads' || str_starts_with($relative, 'public/uploads/')) {
                    continue;
                }
                $absolute = $directory . '/' . $entry;
                if (is_link($absolute)) {
                    throw new RuntimeException("管理領域のシンボリックリンクを拒否しました: {$relative}");
                }
                if (is_dir($absolute)) {
                    $walk($absolute, $relative);
                } elseif (is_file($absolute) && !isset($expectedLowercase[strtolower($relative)])) {
                    throw new RuntimeException("公式配布にない追加ファイルがあります: {$relative}");
                }
            }
        };
        $walk($this->config->basePath);
    }
}
