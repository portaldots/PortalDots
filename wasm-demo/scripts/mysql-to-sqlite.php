<?php

require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $exception): never {
    fwrite(STDERR, (string)$exception . "\n");
    exit(1);
});

use Illuminate\Support\Facades\DB;

$mysql = DB::connection('mysql');
$schema = $mysql->getSchemaBuilder();
$file = getcwd() . '/database/demo.sqlite';
if (file_exists($file)) {
    unlink($file);
}
$sqlite = new PDO('sqlite:' . $file, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$quote = fn ($name) => '"' . str_replace('"', '""', $name) . '"';
$sqlite->exec('PRAGMA foreign_keys = OFF');
$counts = $omitted = [];
foreach ($schema->getTables() as $table) {
    $name = $table['name'];
    $indexes = $schema->getIndexes($name);
    $primary = array_values(array_filter($indexes, fn ($index) => $index['primary']));
    $columns = [];
    foreach ($schema->getColumns($name) as $column) {
        $type = strtolower($column['type_name']);
        $affinity = match (true) {
            str_contains($type, 'int'), in_array($type, ['bool', 'boolean', 'bit']) => 'INTEGER',
            in_array($type, ['decimal', 'numeric', 'float', 'double', 'real']) => 'NUMERIC',
            str_contains($type, 'blob'), in_array($type, ['binary', 'varbinary']) => 'BLOB',
            default => 'TEXT',
        };
        $sql = $quote($column['name']) . ' ' . $affinity;
        if ($column['auto_increment']) {
            $sql = $quote($column['name']) . ' INTEGER PRIMARY KEY AUTOINCREMENT';
        } else {
            if (!$column['nullable']) {
                $sql .= ' NOT NULL';
            }
            if ($column['default'] !== null) {
                $default = $column['default'];
                $sql .= ' DEFAULT ' . (preg_match('/^current_timestamp(?:\(\d*\))?$/i', $default)
                    ? 'CURRENT_TIMESTAMP' : $sqlite->quote($default));
            }
        }
        $columns[] = $sql;
    }
    $autoIncrement = array_filter($schema->getColumns($name), fn ($column) => $column['auto_increment']);
    if ($primary && !$autoIncrement) {
        $columns[] = 'PRIMARY KEY (' . implode(', ', array_map($quote, $primary[0]['columns'])) . ')';
    }
    foreach ($schema->getForeignKeys($name) as $foreign) {
        $columns[] = 'FOREIGN KEY (' . implode(', ', array_map($quote, $foreign['columns'])) . ') REFERENCES '
            . $quote($foreign['foreign_table']) . ' (' . implode(', ', array_map($quote, $foreign['foreign_columns']))
            . ') ON DELETE ' . $foreign['on_delete'] . ' ON UPDATE ' . $foreign['on_update'];
    }
    $sqlite->exec('CREATE TABLE ' . $quote($name) . ' (' . implode(', ', $columns) . ')');
    foreach ($indexes as $index) {
        if ($index['primary']) {
            continue;
        }
        if (in_array($index['type'], ['fulltext', 'spatial'])) {
            $omitted[] = $name . '.' . $index['name'];
            continue;
        }
        $sqlite->exec('CREATE ' . ($index['unique'] ? 'UNIQUE ' : '') . 'INDEX '
            . $quote($name . '_' . $index['name']) . ' ON ' . $quote($name)
            . ' (' . implode(', ', array_map($quote, $index['columns'])) . ')');
    }
    $rows = $mysql->table($name)->get();
    $sqlite->beginTransaction();
    foreach ($rows as $record) {
        $row = (array)$record;
        $columns = implode(', ', array_map($quote, array_keys($row)));
        $placeholders = implode(', ', array_fill(0, count($row), '?'));
        $insert = $sqlite->prepare('INSERT INTO ' . $quote($name) . " ({$columns}) VALUES ({$placeholders})");
        $insert->execute(array_values($row));
    }
    $sqlite->commit();
    $counts[$name] = count($rows);
    if ((int)$sqlite->query('SELECT COUNT(*) FROM ' . $quote($name))->fetchColumn() !== count($rows)) {
        throw new RuntimeException('Row count mismatch: ' . $name);
    }
}
$sqlite->exec('PRAGMA foreign_keys = ON');
$foreignKeyErrors = $sqlite->query('PRAGMA foreign_key_check')->fetchAll();
if ($foreignKeyErrors || $sqlite->query('PRAGMA integrity_check')->fetchColumn() !== 'ok') {
    throw new RuntimeException('SQLite integrity check failed');
}
file_put_contents(dirname(__DIR__) . '/.work/conversion.json', json_encode([
    'rowCounts' => $counts, 'omittedIndexes' => $omitted,
], JSON_PRETTY_PRINT));
echo 'Converted ' . count($counts) . " tables (including migrations); integrity OK\n";
