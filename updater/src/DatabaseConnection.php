<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use PDO;
use RuntimeException;

final class DatabaseConnection
{
    public static function open(Config $config): PDO
    {
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('DB保護に必要な pdo_mysql 拡張がありません。');
        }
        $environment = Config::environment($config->basePath);
        $connection = $environment['DB_CONNECTION'] ?? 'mysql';
        if ($connection !== 'mysql') {
            throw new RuntimeException('ブラウザ更新はMySQL専用DBだけに対応しています。');
        }
        if (isset($environment['DB_URL'])) {
            throw new RuntimeException('DB_URL構成はブラウザ更新の対象外です。');
        }
        $database = $environment['DB_DATABASE'] ?? '';
        if ($database === '' || !preg_match('/^[A-Za-z0-9$_-]+$/', $database)) {
            throw new RuntimeException('DB_DATABASEが未設定または非対応の名前です。');
        }
        $host = $environment['DB_HOST'] ?? '127.0.0.1';
        $port = (int) ($environment['DB_PORT'] ?? 3306);
        $socket = $environment['DB_SOCKET'] ?? '';
        $dsn = $socket !== ''
            ? "mysql:unix_socket={$socket};dbname={$database};charset=utf8mb4"
            : "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $bufferedQueryAttribute = PHP_VERSION_ID >= 80400 && defined('Pdo\\Mysql::ATTR_USE_BUFFERED_QUERY')
            ? constant('Pdo\\Mysql::ATTR_USE_BUFFERED_QUERY') : PDO::MYSQL_ATTR_USE_BUFFERED_QUERY;
        $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                $bufferedQueryAttribute => true,
        ];
        if (($environment['MYSQL_ATTR_SSL_CA'] ?? '') !== '') {
            $sslAttribute = defined('Pdo\\Mysql::ATTR_SSL_CA')
                ? constant('Pdo\\Mysql::ATTR_SSL_CA') : PDO::MYSQL_ATTR_SSL_CA;
            $options[$sslAttribute] = $environment['MYSQL_ATTR_SSL_CA'];
        }
        try {
            $pdo = new PDO($dsn, $environment['DB_USERNAME'] ?? '', $environment['DB_PASSWORD'] ?? '', $options);
            $pdo->exec("SET SESSION time_zone = '+00:00'");
            $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,"
                . "ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            return $pdo;
        } catch (\Throwable $exception) {
            throw new RuntimeException('更新用DB接続を確立できません。', 0, $exception);
        }
    }

    public static function assertMinimumVersion(PDO $pdo, string $minimumMysql): void
    {
        $mysqlVersion = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
        if (stripos($mysqlVersion, 'mariadb') !== false || version_compare($mysqlVersion, $minimumMysql, '<')) {
            throw new RuntimeException('この更新版が要求するMySQLバージョンを満たしていません。');
        }
    }
}
