<?php

declare(strict_types=1);

namespace PortalDots\Updater;

use PDO;
use RuntimeException;

final class DatabaseBackup
{
    private const CHUNK_SIZE = 250;

    public function __construct(private readonly Config $config)
    {
    }

    public function diagnose(): void
    {
        $this->inspect(DatabaseConnection::open($this->config));
    }

    /**
     * Perform one bounded backup or proof-restore unit.
     *
     * @param array<string, mixed> $context
     */
    public function backupStep(array &$context, string $jobPath, string $jobId): bool
    {
        $pdo = DatabaseConnection::open($this->config);
        if (!isset($context['stage'])) {
            $context = [
                'stage' => 'inspect',
                'table_index' => 0,
                'chunk_index' => 0,
                'tables' => [],
                'shadow_map' => [],
            ];
        }

        switch ($context['stage']) {
            case 'inspect':
                $context['tables'] = $this->inspect($pdo);
                $context['shadow_map'] = $this->shadowMap($context['tables'], $jobId);
                $context['stage'] = 'dump';
                return false;

            case 'dump':
                if ($this->dumpStep($pdo, $context, $jobPath)) {
                    $context['stage'] = 'proof_create';
                    $context['table_index'] = 0;
                    $context['chunk_index'] = 0;
                }
                return false;

            case 'proof_create':
                if ($this->createProofTable($pdo, $context)) {
                    $context['stage'] = 'proof_import';
                    $context['table_index'] = 0;
                    $context['chunk_index'] = 0;
                }
                return false;

            case 'proof_import':
                if ($this->importStep($pdo, $context, $jobPath, true)) {
                    $context['stage'] = 'proof_verify';
                    $context['table_index'] = 0;
                    $context['chunk_index'] = 0;
                }
                return false;

            case 'proof_verify':
                if ($this->verifyStep($pdo, $context, $jobPath, true)) {
                    $context['stage'] = 'proof_foreign_keys';
                    $context['table_index'] = 0;
                }
                return false;

            case 'proof_foreign_keys':
                if ($this->verifyForeignKeys($pdo, $context['tables'], $context, true)) {
                    $context['stage'] = 'proof_drop';
                    $context['table_index'] = 0;
                }
                return false;

            case 'proof_drop':
                if ($this->dropProofTable($pdo, $context)) {
                    $context['stage'] = 'complete';
                    return true;
                }
                return false;

            case 'complete':
                return true;
        }
        throw new RuntimeException('DBバックアップ状態が不正です。');
    }

    /**
     * Perform one bounded production restore unit. This method is deliberately repeatable.
     *
     * @param array<string, mixed> $backup
     * @param array<string, mixed> $restore
     */
    public function restoreStep(array $backup, array &$restore, string $jobPath): bool
    {
        $pdo = DatabaseConnection::open($this->config);
        $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS = 0');
        if (!isset($restore['stage'])) {
            $restore = ['stage' => 'verify_backup', 'table_index' => 0, 'chunk_index' => 0];
        }
        switch ($restore['stage']) {
            case 'verify_backup':
                if ($this->verifyBackupStep($backup['tables'], $restore, $jobPath)) {
                    $restore['stage'] = 'list';
                    $restore['table_index'] = 0;
                    $restore['chunk_index'] = 0;
                }
                return false;
            case 'list':
                $restore['current_tables'] = $this->baseTableNames($pdo);
                $restore['stage'] = 'drop';
                return false;
            case 'drop':
                $tables = $restore['current_tables'];
                if ($restore['table_index'] >= count($tables)) {
                    $restore['stage'] = 'create';
                    $restore['table_index'] = 0;
                    return false;
                }
                $pdo->exec('DROP TABLE IF EXISTS ' . $this->quote((string) $tables[$restore['table_index']]));
                $restore['table_index']++;
                return false;
            case 'create':
                if ($restore['table_index'] >= count($backup['tables'])) {
                    $restore['stage'] = 'import';
                    $restore['table_index'] = 0;
                    $restore['chunk_index'] = 0;
                    return false;
                }
                $table = $backup['tables'][$restore['table_index']];
                $pdo->exec('DROP TABLE IF EXISTS ' . $this->quote((string) $table['name']));
                $pdo->exec((string) $table['create_sql']);
                $this->testBarrier('restore-create');
                $restore['table_index']++;
                return false;
            case 'import':
                if ($this->importStep($pdo, $restore, $jobPath, false, $backup['tables'])) {
                    $restore['stage'] = 'verify';
                    $restore['table_index'] = 0;
                    $restore['chunk_index'] = 0;
                }
                return false;
            case 'verify':
                if ($this->verifyStep($pdo, $restore, $jobPath, false, $backup['tables'])) {
                    $restore['stage'] = 'foreign_keys';
                    $restore['table_index'] = 0;
                }
                return false;
            case 'foreign_keys':
                if ($this->verifyForeignKeys($pdo, $backup['tables'], $restore, false)) {
                    $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS = 1');
                    $restore['stage'] = 'complete';
                    return true;
                }
                return false;
            case 'complete':
                return true;
        }
        throw new RuntimeException('DB復元状態が不正です。');
    }

    /** @param array<string, mixed> $backup @param array<string, mixed> $context */
    public function cleanupProofStep(array $backup, array &$context): bool
    {
        $index = (int) ($context['table_index'] ?? 0);
        if ($index >= count($backup['tables'] ?? [])) {
            return true;
        }
        $table = $backup['tables'][$index];
        $shadow = $backup['shadow_map'][$table['name']] ?? null;
        if (is_string($shadow)) {
            $pdo = DatabaseConnection::open($this->config);
            $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS = 0');
            $pdo->exec('DROP TABLE IF EXISTS ' . $this->quote($shadow));
        }
        $context['table_index'] = $index + 1;
        return $context['table_index'] >= count($backup['tables']);
    }

    /** @return list<array<string, mixed>> */
    private function inspect(PDO $pdo): array
    {
        $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
        if (stripos($version, 'mariadb') !== false || version_compare($version, '8.4.0', '<')) {
            throw new RuntimeException('ブラウザ更新にはMySQL 8.4以降が必要です。');
        }
        $database = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
        foreach ([
            ['VIEWS', 'TABLE_SCHEMA', 'ビュー'],
            ['TRIGGERS', 'TRIGGER_SCHEMA', 'トリガー'],
            ['ROUTINES', 'ROUTINE_SCHEMA', 'ルーチン'],
            ['EVENTS', 'EVENT_SCHEMA', 'イベント'],
        ] as [$catalog, $column, $label]) {
            $statement = $pdo->prepare("SELECT COUNT(*) FROM information_schema.{$catalog} WHERE {$column} = ?");
            $statement->execute([$database]);
            if ((int) $statement->fetchColumn() !== 0) {
                throw new RuntimeException("{$label}を含むDBは初版ブラウザ更新の対象外です。");
            }
        }

        $probe = '_pdu_privilege_' . bin2hex(random_bytes(4));
        $pdo->exec('CREATE TABLE ' . $this->quote($probe) . ' (`id` INT NOT NULL PRIMARY KEY) ENGINE=InnoDB');
        $pdo->exec('DROP TABLE ' . $this->quote($probe));

        $tables = [];
        foreach ($this->baseTableNames($pdo) as $name) {
            if (str_starts_with($name, '_pdu_')) {
                throw new RuntimeException('DBに復旧用予約名のテーブルがあります。');
            }
            $tableStatement = $pdo->prepare(
                'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
            );
            $tableStatement->execute([$database, $name]);
            if (strtoupper((string) $tableStatement->fetchColumn()) !== 'INNODB') {
                throw new RuntimeException("InnoDB以外のテーブルは安全に復元できません: {$name}");
            }
            $columnsStatement = $pdo->prepare(
                'SELECT COLUMN_NAME, IS_NULLABLE, EXTRA FROM information_schema.COLUMNS '
                . 'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION'
            );
            $columnsStatement->execute([$database, $name]);
            $columns = $columnsStatement->fetchAll(PDO::FETCH_ASSOC);
            if ($columns === []) {
                throw new RuntimeException("テーブル定義を取得できません: {$name}");
            }
            foreach ($columns as $column) {
                $extra = strtoupper((string) $column['EXTRA']);
                if (str_contains($extra, 'VIRTUAL GENERATED') || str_contains($extra, 'STORED GENERATED')) {
                    throw new RuntimeException("生成列を含むテーブルは初版ブラウザ更新の対象外です: {$name}");
                }
            }
            $primaryStatement = $pdo->prepare(
                "SELECT COLUMN_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? "
                . "AND TABLE_NAME = ? AND INDEX_NAME = 'PRIMARY' ORDER BY SEQ_IN_INDEX"
            );
            $primaryStatement->execute([$database, $name]);
            $primary = $primaryStatement->fetchAll(PDO::FETCH_COLUMN);
            $foreignStatement = $pdo->prepare(
                'SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME '
                . 'FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? '
                . 'AND REFERENCED_TABLE_NAME IS NOT NULL ORDER BY CONSTRAINT_NAME, ORDINAL_POSITION'
            );
            $foreignStatement->execute([$database, $name]);
            $foreignKeys = [];
            foreach ($foreignStatement->fetchAll(PDO::FETCH_ASSOC) as $foreign) {
                $constraint = (string) $foreign['CONSTRAINT_NAME'];
                $foreignKeys[$constraint] ??= [
                    'columns' => [],
                    'referenced_table' => (string) $foreign['REFERENCED_TABLE_NAME'],
                    'referenced_columns' => [],
                ];
                $foreignKeys[$constraint]['columns'][] = (string) $foreign['COLUMN_NAME'];
                $foreignKeys[$constraint]['referenced_columns'][] = (string) $foreign['REFERENCED_COLUMN_NAME'];
            }
            $create = $pdo->query('SHOW CREATE TABLE ' . $this->quote($name))->fetch(PDO::FETCH_NUM);
            if (!is_array($create) || !isset($create[1])) {
                throw new RuntimeException("CREATE TABLEを取得できません: {$name}");
            }
            $count = (int) $pdo->query('SELECT COUNT(*) FROM ' . $this->quote($name))->fetchColumn();
            $tables[] = [
                'name' => $name,
                'create_sql' => $create[1],
                'schema_sha256' => hash('sha256', $create[1]),
                'schema_normalized_sha256' => hash('sha256', $this->normalizeSchema($create[1], [])),
                'columns' => array_column($columns, 'COLUMN_NAME'),
                'primary_key' => array_values($primary),
                'order_columns' => $primary === [] ? array_column($columns, 'COLUMN_NAME') : array_values($primary),
                'foreign_keys' => array_values($foreignKeys),
                'row_count' => $count,
                'chunks' => (int) ceil($count / self::CHUNK_SIZE),
                'data_sha256' => null,
            ];
        }
        return $tables;
    }

    /** @param array<string, mixed> $context */
    private function dumpStep(PDO $pdo, array &$context, string $jobPath): bool
    {
        $tables =& $context['tables'];
        $tableIndex = (int) $context['table_index'];
        if ($tableIndex >= count($tables)) {
            return true;
        }
        $table =& $tables[$tableIndex];
        $chunkIndex = (int) $context['chunk_index'];
        if ($chunkIndex >= $table['chunks']) {
            $table['data_sha256'] = $this->hashChunks($jobPath, $tableIndex, (int) $table['chunks']);
            $context['table_index']++;
            $context['chunk_index'] = 0;
            return $context['table_index'] >= count($tables);
        }
        $path = $this->chunkPath($jobPath, $tableIndex, $chunkIndex);
        if (!is_file($path)) {
            $order = $this->orderBy($table['order_columns']);
            $sql = 'SELECT * FROM ' . $this->quote($table['name']) . ' ORDER BY ' . $order
                . ' LIMIT ' . self::CHUNK_SIZE . ' OFFSET ' . ($chunkIndex * self::CHUNK_SIZE);
            $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_NUM);
            $this->writeChunk($path, $rows);
        }
        $chunkHash = hash_file('sha256', $path);
        if (!is_string($chunkHash)) {
            throw new RuntimeException("DBバックアップを検証できません: {$table['name']}");
        }
        $table['chunk_sha256'][$chunkIndex] = $chunkHash;
        $context['chunk_index']++;
        return false;
    }

    /** @param array<string, mixed> $context */
    private function createProofTable(PDO $pdo, array &$context): bool
    {
        $index = (int) $context['table_index'];
        if ($index >= count($context['tables'])) {
            return true;
        }
        $table = $context['tables'][$index];
        $shadow = $context['shadow_map'][$table['name']];
        $sql = $this->rewriteCreateSql($table['create_sql'], $context['shadow_map'], $shadow, $index);
        $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('DROP TABLE IF EXISTS ' . $this->quote($shadow));
        $pdo->exec($sql);
        $context['table_index']++;
        return $context['table_index'] >= count($context['tables']);
    }

    /**
     * @param array<string, mixed> $context
     * @param list<array<string, mixed>>|null $tables
     */
    private function importStep(
        PDO $pdo,
        array &$context,
        string $jobPath,
        bool $shadow,
        ?array $tables = null,
    ): bool {
        $tables ??= $context['tables'];
        $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS = 0');
        $index = (int) $context['table_index'];
        if ($index >= count($tables)) {
            return true;
        }
        $table = $tables[$index];
        $chunk = (int) $context['chunk_index'];
        if ($chunk >= $table['chunks']) {
            $context['table_index']++;
            $context['chunk_index'] = 0;
            return $context['table_index'] >= count($tables);
        }
        $name = $shadow ? $context['shadow_map'][$table['name']] : $table['name'];
        $rows = $this->readChunk($this->chunkPath($jobPath, $index, $chunk));
        if ($rows !== []) {
            $existing = $pdo->query(
                'SELECT * FROM ' . $this->quote($name) . ' ORDER BY ' . $this->orderBy($table['order_columns'])
                . ' LIMIT ' . self::CHUNK_SIZE . ' OFFSET ' . ($chunk * self::CHUNK_SIZE),
            )->fetchAll(PDO::FETCH_NUM);
            if ($existing !== []) {
                if (!hash_equals(
                    hash('sha256', $this->encodeRows($rows)),
                    hash('sha256', $this->encodeRows($existing)),
                )) {
                    throw new RuntimeException("DB復元済みデータがバックアップと一致しません: {$table['name']}");
                }
                $context['chunk_index']++;
                return false;
            }
            $columns = implode(', ', array_map($this->quote(...), $table['columns']));
            $placeholders = implode(', ', array_fill(0, count($table['columns']), '?'));
            $statement = $pdo->prepare('INSERT INTO ' . $this->quote($name)
                . " ({$columns}) VALUES ({$placeholders})");
            $pdo->beginTransaction();
            try {
                foreach ($rows as $row) {
                    $statement->execute($row);
                }
                $pdo->commit();
            } catch (\Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                throw $exception;
            }
        }
        if (!$shadow) {
            $this->testBarrier('restore-import');
        }
        $context['chunk_index']++;
        return false;
    }

    private function testBarrier(string $name): void
    {
        if ((Config::environment($this->config->basePath)['APP_ENV'] ?? '') !== 'testing'
            || getenv('PORTALDOTS_UPDATER_TEST_BARRIER') !== $name) {
            return;
        }
        $path = $this->config->privatePath . '/test-barrier-' . $name;
        if (file_put_contents($path, "ready\n", LOCK_EX) === false) {
            throw new RuntimeException('Updater test barrier could not be created.');
        }
        while (is_file($path)) {
            usleep(10_000);
        }
    }

    /**
     * @param array<string, mixed> $context
     * @param list<array<string, mixed>>|null $tables
     */
    private function verifyStep(
        PDO $pdo,
        array &$context,
        string $jobPath,
        bool $shadow,
        ?array $tables = null,
    ): bool {
        $tables ??= $context['tables'];
        $index = (int) $context['table_index'];
        if ($index >= count($tables)) {
            return true;
        }
        $table = $tables[$index];
        $name = $shadow ? $context['shadow_map'][$table['name']] : $table['name'];
        $count = (int) $pdo->query('SELECT COUNT(*) FROM ' . $this->quote($name))->fetchColumn();
        if ($count !== (int) $table['row_count']) {
            throw new RuntimeException("DB復元の件数が一致しません: {$table['name']}");
        }
        $chunk = (int) $context['chunk_index'];
        if ($chunk >= $table['chunks']) {
            $create = $pdo->query('SHOW CREATE TABLE ' . $this->quote($name))->fetch(PDO::FETCH_NUM);
            if (!is_array($create)) {
                throw new RuntimeException("DB復元の定義を取得できません: {$table['name']}");
            }
            if (!$shadow) {
                if (!hash_equals(
                    (string) $table['schema_normalized_sha256'],
                    hash('sha256', $this->normalizeSchema((string) $create[1], [])),
                )) {
                    throw new RuntimeException("DB復元の定義が一致しません: {$table['name']}");
                }
            } else {
                $expectedSchema = $this->normalizeSchema((string) $table['create_sql'], $context['shadow_map']);
                $actualSchema = $this->normalizeSchema((string) $create[1], $context['shadow_map']);
                if (!hash_equals(hash('sha256', $expectedSchema), hash('sha256', $actualSchema))) {
                    throw new RuntimeException("DB検証復元の定義が一致しません: {$table['name']}");
                }
            }
            $context['table_index']++;
            $context['chunk_index'] = 0;
            return $context['table_index'] >= count($tables);
        }
        $order = $this->orderBy($table['order_columns']);
        $sql = 'SELECT * FROM ' . $this->quote($name) . ' ORDER BY ' . $order
            . ' LIMIT ' . self::CHUNK_SIZE . ' OFFSET ' . ($chunk * self::CHUNK_SIZE);
        $actual = $pdo->query($sql)->fetchAll(PDO::FETCH_NUM);
        $expected = $this->readChunk($this->chunkPath($jobPath, $index, $chunk));
        if (!hash_equals(hash('sha256', $this->encodeRows($expected)), hash('sha256', $this->encodeRows($actual)))) {
            throw new RuntimeException("DB復元の内容が一致しません: {$table['name']}");
        }
        $context['chunk_index']++;
        return false;
    }

    /** @param array<string, mixed> $context */
    private function dropProofTable(PDO $pdo, array &$context): bool
    {
        $index = (int) $context['table_index'];
        if ($index >= count($context['tables'])) {
            return true;
        }
        $table = $context['tables'][$index];
        $pdo->exec('SET SESSION FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('DROP TABLE IF EXISTS ' . $this->quote($context['shadow_map'][$table['name']]));
        $context['table_index']++;
        return $context['table_index'] >= count($context['tables']);
    }

    /**
     * @param list<array<string, mixed>> $tables
     * @param array<string, mixed> $context
     */
    private function verifyBackupStep(array $tables, array &$context, string $jobPath): bool
    {
        $tableIndex = (int) $context['table_index'];
        if ($tableIndex >= count($tables)) {
            return true;
        }
        $table = $tables[$tableIndex];
        if (!hash_equals((string) $table['schema_sha256'], hash('sha256', (string) $table['create_sql']))) {
            throw new RuntimeException("DBバックアップの定義が破損しています: {$table['name']}");
        }
        $chunk = (int) $context['chunk_index'];
        if ($chunk >= $table['chunks']) {
            if (!hash_equals((string) $table['data_sha256'], $this->hashChunks($jobPath, $tableIndex, $table['chunks']))) {
                throw new RuntimeException("DBバックアップが破損しています: {$table['name']}");
            }
            $context['table_index']++;
            $context['chunk_index'] = 0;
            return $context['table_index'] >= count($tables);
        }
        $path = $this->chunkPath($jobPath, $tableIndex, $chunk);
        $actual = is_file($path) ? hash_file('sha256', $path) : false;
        if (!is_string($actual) || !hash_equals((string) $table['chunk_sha256'][$chunk], $actual)) {
            throw new RuntimeException("DBバックアップのチャンクが破損しています: {$table['name']}");
        }
        $context['chunk_index']++;
        return false;
    }

    /**
     * @param list<array<string, mixed>> $tables
     * @param array<string, mixed> $context
     */
    private function verifyForeignKeys(PDO $pdo, array $tables, array &$context, bool $shadow): bool
    {
        $index = (int) $context['table_index'];
        if ($index >= count($tables)) {
            return true;
        }
        $table = $tables[$index];
        $child = $shadow ? $context['shadow_map'][$table['name']] : $table['name'];
        foreach ($table['foreign_keys'] as $foreign) {
            $parent = $shadow
                ? $context['shadow_map'][$foreign['referenced_table']] : $foreign['referenced_table'];
            $join = [];
            $notNull = [];
            foreach ($foreign['columns'] as $position => $column) {
                $join[] = 'p.' . $this->quote($foreign['referenced_columns'][$position])
                    . ' = c.' . $this->quote($column);
                $notNull[] = 'c.' . $this->quote($column) . ' IS NOT NULL';
            }
            $query = 'SELECT COUNT(*) FROM ' . $this->quote($child) . ' c WHERE '
                . implode(' AND ', $notNull) . ' AND NOT EXISTS (SELECT 1 FROM '
                . $this->quote($parent) . ' p WHERE ' . implode(' AND ', $join) . ')';
            if ((int) $pdo->query($query)->fetchColumn() !== 0) {
                throw new RuntimeException("DB復元後の外部キー整合性を確認できません: {$table['name']}");
            }
        }
        $context['table_index']++;
        return $context['table_index'] >= count($tables);
    }

    /** @param list<array<string, mixed>> $tables @return array<string, string> */
    private function shadowMap(array $tables, string $jobId): array
    {
        $map = [];
        foreach ($tables as $index => $table) {
            $map[$table['name']] = '_pdu_' . substr($jobId, 0, 8) . '_' . $index;
        }
        if (count(array_unique($map)) !== count($map)) {
            throw new RuntimeException('DB検証用テーブル名が衝突しました。');
        }
        return $map;
    }

    /** @param array<string, string> $map */
    private function rewriteCreateSql(string $sql, array $map, string $shadow, int $tableIndex): string
    {
        $sql = preg_replace('/^CREATE TABLE `[^`]+`/', 'CREATE TABLE ' . $this->quote($shadow), $sql, 1);
        foreach ($map as $original => $mapped) {
            $sql = str_replace('REFERENCES ' . $this->quote($original), 'REFERENCES ' . $this->quote($mapped), $sql);
        }
        $constraint = 0;
        $sql = preg_replace_callback('/CONSTRAINT `[^`]+`/', function () use ($tableIndex, &$constraint): string {
            return 'CONSTRAINT ' . $this->quote('_pduc_' . $tableIndex . '_' . $constraint++);
        }, (string) $sql);
        return (string) $sql;
    }

    /** @param array<string, string> $shadowMap */
    private function normalizeSchema(string $sql, array $shadowMap): string
    {
        foreach ($shadowMap as $original => $shadow) {
            $sql = str_replace($this->quote($shadow), $this->quote($original), $sql);
        }
        $sql = preg_replace('/CONSTRAINT `[^`]+` /', 'CONSTRAINT ', $sql);
        $sql = preg_replace('/ CHARACTER SET [A-Za-z0-9_]+(?= COLLATE)/', '', (string) $sql);
        return (string) $sql;
    }

    /** @return list<string> */
    private function baseTableNames(PDO $pdo): array
    {
        $statement = $pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES "
            . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");
        return array_values(array_map('strval', $statement->fetchAll(PDO::FETCH_COLUMN)));
    }

    /** @param list<array<int, mixed>> $rows */
    private function writeChunk(string $path, array $rows): void
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('DBバックアップ保存先を作成できません。');
        }
        $temporary = $path . '.tmp.' . bin2hex(random_bytes(4));
        $handle = fopen($temporary, 'xb');
        if ($handle === false) {
            throw new RuntimeException('DBバックアップを書き込めません。');
        }
        @chmod($temporary, 0600);
        foreach ($rows as $row) {
            $encoded = $this->encodeRow($row) . "\n";
            if (fwrite($handle, $encoded) !== strlen($encoded)) {
                fclose($handle);
                @unlink($temporary);
                throw new RuntimeException('DBバックアップの書き込みが中断しました。');
            }
        }
        if (!fflush($handle) || (function_exists('fsync') && !fsync($handle))) {
            fclose($handle);
            @unlink($temporary);
            throw new RuntimeException('DBバックアップを永続化できません。');
        }
        fclose($handle);
        if (!rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('DBバックアップを確定できません。');
        }
    }

    /** @return list<array<int, mixed>> */
    private function readChunk(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('DBバックアップのチャンクがありません。');
        }
        $rows = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $encoded = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            $row = [];
            foreach ($encoded as $value) {
                if ($value === null) {
                    $row[] = null;
                    continue;
                }
                $decoded = base64_decode((string) ($value['base64'] ?? ''), true);
                if (!is_string($decoded)) {
                    throw new RuntimeException('DBバックアップの値が破損しています。');
                }
                $row[] = $decoded;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /** @param array<int, mixed> $row */
    private function encodeRow(array $row): string
    {
        return CanonicalJson::encode(array_map(
            static fn ($value): ?array => $value === null ? null : ['base64' => base64_encode((string) $value)],
            $row,
        ));
    }

    /** @param list<array<int, mixed>> $rows */
    private function encodeRows(array $rows): string
    {
        return implode("\n", array_map($this->encodeRow(...), $rows));
    }

    private function hashChunks(string $jobPath, int $table, int $chunks): string
    {
        $hash = hash_init('sha256');
        for ($chunk = 0; $chunk < $chunks; $chunk++) {
            $handle = fopen($this->chunkPath($jobPath, $table, $chunk), 'rb');
            if ($handle === false) {
                throw new RuntimeException('DBバックアップのチャンクがありません。');
            }
            hash_update_stream($hash, $handle);
            fclose($handle);
        }
        return hash_final($hash);
    }

    private function chunkPath(string $jobPath, int $table, int $chunk): string
    {
        return $jobPath . '/database/' . $table . '/' . $chunk . '.jsonl';
    }

    /** @param list<string> $columns */
    private function orderBy(array $columns): string
    {
        $parts = [];
        foreach ($columns as $column) {
            $quoted = $this->quote($column);
            $parts[] = $quoted . ' IS NULL';
            $parts[] = 'HEX(' . $quoted . ')';
        }
        return implode(', ', $parts);
    }

    private function quote(string $identifier): string
    {
        if ($identifier === '' || str_contains($identifier, "\0")) {
            throw new RuntimeException('DB識別子が不正です。');
        }
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
