<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use RuntimeException;

final class Preflight
{
    public function __construct(private readonly Config $config)
    {
    }

    /**
     * @param array<string, mixed> $signed
     * @param list<array{path: string, size: int}> $files
     * @param array<string, mixed> $filePlan
     */
    public function run(array $signed, array $files, array $filePlan): void
    {
        foreach (['curl', 'json', 'pdo_mysql', 'sodium', 'zip'] as $extension) {
            if (!extension_loaded($extension)) {
                throw new RuntimeException("PHP拡張 {$extension} がありません。");
            }
        }
        if (PHP_INT_SIZE < 8) {
            throw new RuntimeException('64bit版PHPが必要です。');
        }
        if (!is_dir($this->config->privatePath) || !is_writable($this->config->privatePath)) {
            throw new RuntimeException('非公開バックアップ領域へ書き込めません。');
        }
        $realPrivate = realpath($this->config->privatePath);
        if ($realPrivate === false) {
            throw new RuntimeException('バックアップ領域を確認できません。');
        }
        $realPublic = realpath($this->config->basePath . '/public');
        if ($realPublic !== false && ($realPrivate === $realPublic
            || str_starts_with($realPrivate . '/', $realPublic . '/'))) {
            throw new RuntimeException('バックアップ領域が公開ディレクトリ内にあります。');
        }
        PrivateStorage::ensure($this->config->privatePath);
        (new HttpProbe($this->config))->verifyPrivateStorage();
        if (!is_writable($this->config->basePath)) {
            throw new RuntimeException('PortalDotsの配置先へ書き込めません。');
        }
        if (filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOL)
            && !function_exists('opcache_invalidate')) {
            throw new RuntimeException('OPcacheを更新後に無効化できません。');
        }

        $pdo = DatabaseConnection::open($this->config);
        $mysqlVersion = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
        if (stripos($mysqlVersion, 'mariadb') !== false
            || version_compare($mysqlVersion, (string) $signed['minimum_mysql'], '<')) {
            throw new RuntimeException('この更新版が要求するMySQLバージョンを満たしていません。');
        }
        (new DatabaseBackup($this->config))->diagnose();
        $databaseBytes = (int) $pdo->query(
            'SELECT COALESCE(SUM(DATA_LENGTH + INDEX_LENGTH), 0) FROM information_schema.TABLES '
            . 'WHERE TABLE_SCHEMA = DATABASE()'
        )->fetchColumn();
        $fileBytes = 0;
        foreach ($filePlan['backup'] as $path) {
            $size = filesize($this->config->basePath . '/' . $path);
            $fileBytes += is_int($size) ? $size : 0;
        }
        $required = ((int) $signed['artifact']['size'] * 2)
            + array_sum(array_column($files, 'size')) + ($databaseBytes * 3) + ($fileBytes * 2) + 67_108_864;
        $available = disk_free_space($this->config->privatePath);
        if (!is_float($available) && !is_int($available)) {
            throw new RuntimeException('空き容量を確認できません。');
        }
        if ($available < $required) {
            throw new RuntimeException('更新・復元用の空き容量が不足しています。');
        }
    }
}
