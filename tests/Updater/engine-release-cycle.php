#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\Config;
use PortalDots\Updater\Engine;
use PortalDots\Updater\FileBackup;
use PortalDots\Updater\JobFactory;
use PortalDots\Updater\StateStore;
use PortalDots\Updater\VersionReader;

if (($argv[1] ?? null) === '--step') {
    if ($argc !== 4) {
        exit(64);
    }
    $root = $argv[2];
    $port = (int) $argv[3];
    $_SERVER['HTTP_HOST'] = '127.0.0.1:' . $port;
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['SERVER_PORT'] = $port;
    $config = fixtureConfig($root, $port);
    $store = new StateStore($config);
    $id = $store->activeId();
    if ($id === null) {
        exit(0);
    }
    (new Engine($config, $store))->step($id);
    exit(0);
}

if ($argc !== 7) {
    fwrite(STDERR, "Usage: engine-release-cycle.php SIGNED_JSON UPDATE_ZIP OLD_RELEASE_ZIP MYSQL_HOST MYSQL_PORT MYSQL_DATABASE\n");
    exit(64);
}
[, $signedPath, $updateZip, $oldZip, $databaseHost, $databasePort, $databaseName] = $argv;
$databaseUser = (string) getenv('UPDATER_MYSQL_USER');
$databasePassword = (string) getenv('UPDATER_MYSQL_PASSWORD');
$signed = json_decode((string) file_get_contents($signedPath), true, 128, JSON_THROW_ON_ERROR);
if (!is_array($signed) || !in_array(readOldVersion($oldZip), $signed['from_versions'] ?? [], true)) {
    throw new RuntimeException('Old release is not an explicitly supported update source.');
}

foreach (['rollback-after-migration', 'success'] as $mode) {
    $root = sys_get_temp_dir() . '/portaldots-engine-' . bin2hex(random_bytes(8));
    mkdir($root, 0700, true);
    safeExtract($oldZip, $root);
    ensureRuntimeDirectories($root);
    $oldVersion = VersionReader::current($root);
    writeEnvironment($root, $databaseHost, $databasePort, $databaseName, $databaseUser, $databasePassword);
    resetDatabase($databaseHost, $databasePort, $databaseName, $databaseUser, $databasePassword);
    runCommand([PHP_BINARY, $root . '/artisan', 'migrate', '--force'], $root);
    seedDatabase($databaseHost, $databasePort, $databaseName, $databaseUser, $databasePassword);
    $baselineData = fixtureData($databaseHost, $databasePort, $databaseName, $databaseUser, $databasePassword);
    (new FileBackup(new Config(
        $root,
        $root . '/storage/app/updater/private',
        'https://example.test/manifest',
        ['example.test'],
        $root . '/updater/keys/release.pub',
    )))->verifyInstallation(json_decode(
        (string) file_get_contents($root . '/.portaldots-manifest.json'),
        true,
        128,
        JSON_THROW_ON_ERROR,
    ));

    [$port, $process, $log] = startServer($root);
    try {
        $config = fixtureConfig($root, $port);
        $_SERVER['HTTP_HOST'] = '127.0.0.1:' . $port;
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = $port;
        $store = new StateStore($config);
        [$cycleSigned, $cycleZip] = $mode === 'success'
            ? [$signed, $updateZip]
            : interruptedMigrationArtifact($signed, $updateZip, $oldVersion, $root);
        $verified = [
            'signed' => $cycleSigned,
            'digest' => hash('sha256', CanonicalJson::encode($cycleSigned)),
            'signing_key_id' => 'release-fixture',
        ];
        $created = (new JobFactory($config, $store))->create(
            ['id' => 'release-fixture', 'email' => 'fixture@example.test'],
            $verified,
        );
        $jobId = $created['job_id'];
        copy($cycleZip, $store->jobPath($jobId, 'update.zip'));
        $state = $store->load($jobId);
        $state['current_step'] = 'inspect_package';
        $state['download'] = ['downloaded' => filesize($cycleZip)];
        $store->save($state);

        $forced = false;
        $killedRestoreStages = [];
        for ($request = 0; $request < 10_000; $request++) {
            $state = $store->load($jobId);
            if ($mode === 'rollback-after-migration' && !$forced && $state['phase'] === 'updating'
                && $state['current_step'] === 'migrate_database') {
                $stepProcess = startStepProcess($root, $port);
                waitForTable($databaseHost, $databasePort, $databaseName, $databaseUser, $databasePassword,
                    'updater_interrupted_side_effect');
                proc_terminate($stepProcess, 9);
                proc_close($stepProcess);
                $forced = true;
                continue;
            }
            $restoreStage = $state['restore']['database']['stage'] ?? null;
            $restoreTableIndex = (int) ($state['restore']['database']['table_index'] ?? 0);
            $restoreChunkIndex = (int) ($state['restore']['database']['chunk_index'] ?? 0);
            $restoreTable = $state['database_backup']['tables'][$restoreTableIndex] ?? null;
            $hasImportChunk = is_array($restoreTable)
                && $restoreChunkIndex < (int) ($restoreTable['chunks'] ?? 0);
            if ($mode === 'rollback-after-migration' && $state['phase'] === 'restoring'
                && in_array($restoreStage, ['create', 'import'], true)
                && ($restoreStage !== 'import' || $hasImportChunk)
                && !isset($killedRestoreStages[$restoreStage])) {
                $barrier = 'restore-' . $restoreStage;
                $stepProcess = startStepProcess($root, $port, $barrier);
                waitForFile($config->privatePath . '/test-barrier-' . $barrier);
                proc_terminate($stepProcess, 9);
                proc_close($stepProcess);
                unlink($config->privatePath . '/test-barrier-' . $barrier);
                $killedRestoreStages[$restoreStage] = true;
                continue;
            }
            runCommand([PHP_BINARY, __FILE__, '--step', $root, (string) $port], dirname(__DIR__, 2));
            $state = $store->load($jobId);
            if (in_array($state['phase'], ['completed', 'rolled_back', 'failed'], true)) {
                break;
            }
        }

        $expectedPhase = $mode === 'success' ? 'completed' : 'rolled_back';
        if (($state['phase'] ?? null) !== $expectedPhase) {
            throw new RuntimeException("Engine {$mode} ended in " . (string) ($state['phase'] ?? 'unknown'));
        }
        $expectedVersion = $mode === 'success' ? $signed['target_version'] : $oldVersion;
        if (VersionReader::current($root) !== $expectedVersion) {
            throw new RuntimeException("Engine {$mode} left the wrong application version.");
        }
        if ($mode !== 'success') {
            $table = pdo($databaseHost, $databasePort, $databaseName, $databaseUser, $databasePassword)
                ->query("SHOW TABLES LIKE 'updater_interrupted_side_effect'")->fetchColumn();
            if ($table !== false) {
                throw new RuntimeException('Interrupted database side effect remained after restore.');
            }
            if ($baselineData !== fixtureData(
                $databaseHost,
                $databasePort,
                $databaseName,
                $databaseUser,
                $databasePassword,
            )) {
                throw new RuntimeException('Non-empty database fixture did not match after restore.');
            }
            (new FileBackup($config))->verifyInstallation(json_decode(
                (string) file_get_contents($root . '/.portaldots-manifest.json'),
                true,
                128,
                JSON_THROW_ON_ERROR,
            ));
        }
        if (is_file($config->privatePath . '/maintenance.json')) {
            throw new RuntimeException("Engine {$mode} left maintenance enabled after verification.");
        }
        fwrite(STDOUT, "ok - engine {$mode}\n");
    } catch (Throwable $exception) {
        fwrite(STDERR, "Server log: {$log}\n");
        throw $exception;
    } finally {
        proc_terminate($process);
        proc_close($process);
        removeTree($root);
    }
}

function seedDatabase(string $host, string $port, string $database, string $user, string $password): void
{
    $pdo = pdo($host, $port, $database, $user, $password);
    $pdo->exec('CREATE TABLE updater_fixture_parent ('
        . 'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, label VARCHAR(100) NOT NULL'
        . ') ENGINE=InnoDB AUTO_INCREMENT=40');
    $pdo->exec('CREATE TABLE updater_fixture_child ('
        . 'id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, parent_id BIGINT UNSIGNED NOT NULL, '
        . 'nullable_value VARBINARY(255) NULL, payload BLOB NOT NULL, '
        . 'CONSTRAINT updater_fixture_fk FOREIGN KEY(parent_id) REFERENCES updater_fixture_parent(id)'
        . ') ENGINE=InnoDB AUTO_INCREMENT=90');
    $pdo->exec("INSERT INTO updater_fixture_parent(id,label) VALUES (31,'日本語')");
    $insert = $pdo->prepare(
        'INSERT INTO updater_fixture_child(id,parent_id,nullable_value,payload) VALUES (?,?,?,?)',
    );
    $insert->execute([81, 31, null, "\x00\xFFbinary"]);
}

/** @return array<string, mixed> */
function fixtureData(string $host, string $port, string $database, string $user, string $password): array
{
    $pdo = pdo($host, $port, $database, $user, $password);
    $row = $pdo->query(
        'SELECT p.id, p.label, c.id child_id, c.nullable_value, HEX(c.payload) payload '
        . 'FROM updater_fixture_parent p JOIN updater_fixture_child c ON c.parent_id=p.id',
    )->fetch(PDO::FETCH_ASSOC);
    $auto = $pdo->prepare(
        'SELECT TABLE_NAME,AUTO_INCREMENT FROM information_schema.TABLES '
        . "WHERE TABLE_SCHEMA=? AND TABLE_NAME IN ('updater_fixture_parent','updater_fixture_child') "
        . 'ORDER BY TABLE_NAME',
    );
    $auto->execute([$database]);
    return ['row' => $row, 'auto_increment' => $auto->fetchAll(PDO::FETCH_ASSOC)];
}

function ensureRuntimeDirectories(string $root): void
{
    foreach ([
        'bootstrap/cache',
        'storage/app/public',
        'storage/app/updater/private',
        'storage/framework/cache/data',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
    ] as $relative) {
        $path = $root . '/' . $relative;
        if (!is_dir($path) && !mkdir($path, 0700, true) && !is_dir($path)) {
            throw new RuntimeException("Cannot create runtime directory {$relative}.");
        }
    }
}

function fixtureConfig(string $root, int $port): Config
{
    return new Config(
        $root,
        $root . '/storage/app/updater/private',
        'https://example.test/manifest',
        ['example.test'],
        $root . '/updater/keys/release.pub',
        applicationUrl: 'http://127.0.0.1:' . $port,
        allowInsecureLocalhost: true,
    );
}

/** @param array<string, mixed> $signed @return array{array<string, mixed>, string} */
function interruptedMigrationArtifact(array $signed, string $archive, string $oldVersion, string $root): array
{
    $path = 'database/migrations/9999_12_31_235959_updater_interruption_fixture.php';
    $source = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE TABLE updater_interrupted_side_effect (id INT PRIMARY KEY) ENGINE=InnoDB');
        DB::statement('SELECT SLEEP(30)');
    }

    public function down(): void
    {
        DB::statement('DROP TABLE IF EXISTS updater_interrupted_side_effect');
    }
};
PHP;
    $target = $root . '/storage/app/updater/private/interrupted-update.zip';
    copy($archive, $target);
    $zip = new ZipArchive();
    if ($zip->open($target) !== true || !$zip->addFromString($path, $source) || !$zip->close()) {
        throw new RuntimeException('Cannot create interrupted migration fixture artifact.');
    }
    $signed['files'][] = [
        'path' => $path,
        'sha256' => hash('sha256', $source),
        'size' => strlen($source),
        'mode' => 0644,
    ];
    usort($signed['files'], static fn (array $left, array $right): int => strcmp($left['path'], $right['path']));
    $signed['migrations'][] = [
        'path' => $path,
        'sha256' => hash('sha256', $source),
        'verify' => ['SELECT 1'],
        'from_versions' => [$oldVersion],
    ];
    $signed['artifact']['size'] = filesize($target);
    $signed['artifact']['sha256'] = hash_file('sha256', $target);
    return [$signed, $target];
}

/** @return resource */
function startStepProcess(string $root, int $port, ?string $barrier = null)
{
    $environment = null;
    if ($barrier !== null) {
        $environment = array_merge($_ENV, ['PORTALDOTS_UPDATER_TEST_BARRIER' => $barrier]);
    }
    $process = proc_open([PHP_BINARY, __FILE__, '--step', $root, (string) $port], [
        0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w'],
    ], $pipes, dirname(__DIR__, 2), $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start updater step process.');
    }
    return $process;
}

function waitForFile(string $path): void
{
    $deadline = microtime(true) + 20;
    do {
        if (is_file($path)) {
            return;
        }
        usleep(50_000);
    } while (microtime(true) < $deadline);
    throw new RuntimeException("Timed out waiting for test barrier {$path}.");
}

function waitForTable(
    string $host,
    string $port,
    string $database,
    string $user,
    string $password,
    string $table,
): void {
    $deadline = microtime(true) + 20;
    do {
        $statement = pdo($host, $port, $database, $user, $password)
            ->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?');
        $statement->execute([$database, $table]);
        if ((int) $statement->fetchColumn() === 1) {
            return;
        }
        usleep(100_000);
    } while (microtime(true) < $deadline);
    throw new RuntimeException('Timed out waiting for the interrupted migration DDL.');
}

function readOldVersion(string $archive): string
{
    $zip = new ZipArchive();
    if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
        throw new RuntimeException('Cannot open old release ZIP.');
    }
    $source = $zip->getFromName('app/ReleaseInfo.php');
    $zip->close();
    if (!is_string($source) || !preg_match("/public const VERSION = '([^']+)'/", $source, $match)) {
        throw new RuntimeException('Cannot read old release version.');
    }
    return $match[1];
}

function safeExtract(string $archive, string $target): void
{
    $zip = new ZipArchive();
    if ($zip->open($archive, ZipArchive::RDONLY) !== true) {
        throw new RuntimeException('Cannot open old release ZIP.');
    }
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $stat = $zip->statIndex($index);
        $directory = str_ends_with((string) $stat['name'], '/');
        $path = \PortalDots\Updater\ZipPackage::normalizePath(
            $directory ? rtrim((string) $stat['name'], '/') : (string) $stat['name'],
        );
        $attributes = $zip->getExternalAttributesIndex($index, $opsys, $external) ? ($external >> 16) & 0170000 : 0;
        if ($attributes === 0120000) {
            throw new RuntimeException('Old release ZIP contains a symbolic link.');
        }
        $destination = $target . '/' . $path;
        if ($directory) {
            mkdir($destination, 0700, true);
            continue;
        }
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0700, true);
        }
        $source = $zip->getStream($stat['name']);
        $output = fopen($destination, 'xb');
        if ($source === false || $output === false || stream_copy_to_stream($source, $output) !== $stat['size']) {
            throw new RuntimeException("Cannot extract {$path}.");
        }
        fclose($source);
        fclose($output);
    }
    $zip->close();
}

function writeEnvironment(string $root, string $host, string $port, string $database, string $user, string $password): void
{
    $key = base64_encode(random_bytes(32));
    file_put_contents($root . '/.env', implode("\n", [
        'APP_NOT_INSTALLED=false', 'APP_ENV=testing', 'APP_KEY=base64:' . $key, 'APP_DEBUG=false',
        'APP_URL=http://127.0.0.1', 'PORTAL_UNIVEMAIL_DOMAIN_PART=example.test',
        'DB_CONNECTION=mysql', 'DB_HOST=' . $host, 'DB_PORT=' . $port, 'DB_DATABASE=' . $database,
        'DB_USERNAME=' . $user, 'DB_PASSWORD=' . $password, 'CACHE_DRIVER=array', 'SESSION_DRIVER=array',
        'QUEUE_CONNECTION=sync', 'MAIL_MAILER=array',
    ]) . "\n");
}

function resetDatabase(string $host, string $port, string $database, string $user, string $password): void
{
    $pdo = pdo($host, $port, $database, $user, $password);
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
        $pdo->exec('DROP TABLE `' . str_replace('`', '``', (string) $table) . '`');
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

function pdo(string $host, string $port, string $database, string $user, string $password): PDO
{
    return new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
}

/** @param list<string> $command */
function runCommand(array $command, string $directory): void
{
    $process = proc_open($command, [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $directory);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start command.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
        throw new RuntimeException("Command failed: {$stdout}\n{$stderr}");
    }
}

/** @return array{int, resource, string} */
function startServer(string $root): array
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
    if ($socket === false) {
        throw new RuntimeException("Cannot reserve HTTP port: {$errorCode} {$errorMessage}");
    }
    $name = stream_socket_get_name($socket, false);
    $port = (int) substr((string) $name, strrpos((string) $name, ':') + 1);
    fclose($socket);
    $log = sys_get_temp_dir() . '/portaldots-engine-http-' . $port . '.log';
    $process = proc_open([PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', $root . '/public'], [
        0 => ['file', '/dev/null', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a'],
    ], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Cannot start updater HTTP fixture.');
    }
    $deadline = microtime(true) + 5;
    do {
        $probe = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.1);
        if (is_resource($probe)) {
            fclose($probe);
            return [$port, $process, $log];
        }
        usleep(50_000);
    } while (microtime(true) < $deadline);
    proc_terminate($process);
    proc_close($process);
    throw new RuntimeException('Updater HTTP fixture did not start.');
}

function removeTree(string $path): void
{
    if (!is_dir($path)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $item) {
        $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}
