#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\CanonicalJson;
use PortalDots\Updater\Config;
use PortalDots\Updater\DatabaseBackup;
use PortalDots\Updater\FileBackup;
use PortalDots\Updater\ManifestVerifier;
use PortalDots\Updater\RecoveryAuth;
use PortalDots\Updater\StateStore;
use PortalDots\Updater\TrustedKeyStore;
use PortalDots\Updater\UpdateGate;
use PortalDots\Updater\ZipPackage;

$tests = [];
$tests['signed manifest rejects tampering and replay'] = static function (): void {
    $root = fixtureRoot();
    try {
        mkdir($root . '/updater/keys', 0700, true);
        mkdir($root . '/storage/app/updater/private', 0700, true);
        $keys = sodium_crypto_sign_keypair();
        $public = sodium_crypto_sign_publickey($keys);
        $secret = sodium_crypto_sign_secretkey($keys);
        file_put_contents($root . '/updater/keys/release.pub', base64_encode($public));
        $config = new Config($root, $root . '/storage/app/updater/private', 'https://example.test/manifest',
            ['example.test'], $root . '/updater/keys/release.pub');
        $store = new StateStore($config);
        $verifier = new ManifestVerifier($config, new TrustedKeyStore($config, $store));
        $signed = signedFixture();
        $payload = CanonicalJson::encode($signed);
        $envelope = ['signed' => $signed, 'signatures' => [[
            'keyid' => hash('sha256', $public),
            'sig' => base64_encode(sodium_crypto_sign_detached($payload, $secret)),
        ]]];
        $verified = $verifier->verify($envelope, '6.0.0');
        assertSame('6.0.1', $verified['signed']['target_version']);
        expectFailure(static function () use ($verifier, $envelope): void {
            $envelope['signed']['target_version'] = '6.0.2';
            $verifier->verify($envelope, '6.0.0');
        });
        expectFailure(static fn () => $verifier->verify($envelope, '6.0.0', 2, str_repeat('0', 64)));
        $expired = signedFixture();
        $expired['issued_at'] = gmdate(DATE_ATOM, time() - 7200);
        $expired['expires_at'] = gmdate(DATE_ATOM, time() - 3600);
        $expiredPayload = CanonicalJson::encode($expired);
        expectFailure(static fn () => $verifier->verify([
            'signed' => $expired,
            'signatures' => [[
                'keyid' => hash('sha256', $public),
                'sig' => base64_encode(sodium_crypto_sign_detached($expiredPayload, $secret)),
            ]],
        ], '6.0.0'));

        file_put_contents($root . '/updater/keys/release.pub', 'UNCONFIGURED');
        assertSame([], (new TrustedKeyStore($config, $store))->all(2));
    } finally {
        removeTree($root);
    }
};

$tests['zip rejects traversal and extracts exact bytes'] = static function (): void {
    $root = fixtureRoot();
    try {
        mkdir($root . '/private', 0700, true);
        $config = new Config($root, $root . '/private', 'https://example.test/manifest', ['example.test'],
            $root . '/missing.pub');
        $archive = $root . '/valid.zip';
        $zip = new ZipArchive();
        $zip->open($archive, ZipArchive::CREATE);
        $zip->addFromString('app/test.php', "<?php\nreturn 1;\n");
        $zip->close();
        $contents = "<?php\nreturn 1;\n";
        $files = [['path' => 'app/test.php', 'sha256' => hash('sha256', $contents), 'size' => strlen($contents), 'mode' => 0644]];
        $package = new ZipPackage($config);
        $package->inspect($archive, $files);
        $context = [];
        assertTrue($package->extractStep($archive, $root . '/stage', $files, $context));
        assertSame($contents, file_get_contents($root . '/stage/app/test.php'));

        $bad = $root . '/bad.zip';
        $zip->open($bad, ZipArchive::CREATE);
        $zip->addFromString('../escape.php', 'bad');
        $zip->close();
        expectFailure(static fn () => $package->inspect($bad, []));

        $duplicate = $root . '/duplicate.zip';
        $zip->open($duplicate, ZipArchive::CREATE);
        $zip->addFromString('app/Duplicate.php', 'a');
        $zip->addFromString('app/duplicate.php', 'b');
        $zip->close();
        expectFailure(static fn () => $package->inspect($duplicate, []));

        $link = $root . '/link.zip';
        $zip->open($link, ZipArchive::CREATE);
        $zip->addFromString('app/link.php', 'target');
        $zip->setExternalAttributesName('app/link.php', ZipArchive::OPSYS_UNIX, 0120777 << 16);
        $zip->close();
        expectFailure(static fn () => $package->inspect($link, []));

        $smallConfig = new Config($root, $root . '/private', 'https://example.test/manifest', ['example.test'],
            $root . '/missing.pub', maxExtractedBytes: 4);
        expectFailure(static fn () => (new ZipPackage($smallConfig))->inspect($archive, $files));
    } finally {
        removeTree($root);
    }
};

$tests['recovery code is hashed, rate limited, and session bound'] = static function (): void {
    $root = fixtureRoot();
    try {
        mkdir($root . '/private', 0700, true);
        $config = new Config($root, $root . '/private', 'https://example.test/manifest', ['example.test'],
            $root . '/missing.pub');
        $store = new StateStore($config);
        $id = bin2hex(random_bytes(16));
        $code = '00112233-44556677-8899AABB-CCDDEEFF';
        $store->create([
            'id' => $id,
            'recovery_hash' => password_hash($code, PASSWORD_BCRYPT),
            'sessions' => [],
            'auth_failures' => 0,
            'auth_locked_until' => null,
        ]);
        $auth = new RecoveryAuth($store);
        $token = $auth->authenticate($id, $code, 'test-agent');
        $state = $store->load($id);
        assertTrue(!str_contains((string) file_get_contents($store->jobPath($id, 'state.json')), $code));
        assertTrue($auth->authorize($state, $token, 'test-agent'));
        assertTrue(!$auth->authorize($state, $token, 'different-agent'));
        assertTrue(hash_equals(hash_hmac('sha256', 'csrf:' . $id, $token), $auth->csrf($id, $token)));

        for ($attempt = 0; $attempt < 5; $attempt++) {
            expectFailure(static fn () => $auth->authenticate($id, 'WRONG-CODE', 'test-agent'));
        }
        expectFailure(static fn () => $auth->authenticate($id, $code, 'test-agent'));
    } finally {
        removeTree($root);
    }
};

$tests['job creation and traffic gate reject concurrent work'] = static function (): void {
    $root = fixtureRoot();
    try {
        mkdir($root . '/storage/app/updater/private', 0700, true);
        mkdir($root . '/app', 0700, true);
        file_put_contents($root . '/app/ReleaseInfo.php', "<?php const PORTAL_VERSION = '6.0.0';\n");
        $config = new Config($root, $root . '/storage/app/updater/private', 'https://example.test/manifest',
            ['example.test'], $root . '/missing.pub');
        $store = new StateStore($config);
        $id = bin2hex(random_bytes(16));
        $state = [
            'id' => $id,
            'actor' => ['id' => 1],
            'from_version' => '6.0.0',
            'recovery_hash' => password_hash('CODE', PASSWORD_BCRYPT),
        ];
        $store->create($state);
        expectFailure(static fn () => $store->create(array_replace($state, ['id' => bin2hex(random_bytes(16))])));

        UpdateGate::beginMaintenance($config, $id);
        assertTrue(!UpdateGate::enter($root));
        UpdateGate::endMaintenance($config);
        $lease = fopen($config->privatePath . '/traffic.lock', 'c+');
        assertTrue(is_resource($lease) && flock($lease, LOCK_SH));
        assertTrue(!UpdateGate::waitForDrain($config));
        flock($lease, LOCK_UN);
        fclose($lease);
        assertTrue(UpdateGate::waitForDrain($config));
    } finally {
        removeTree($root);
    }
};

$tests['trusted signing keys activate and retire only at authorized sequences'] = static function (): void {
    $root = fixtureRoot();
    try {
        mkdir($root . '/updater/keys', 0700, true);
        mkdir($root . '/storage/app/updater/private', 0700, true);
        $first = sodium_crypto_sign_publickey(sodium_crypto_sign_keypair());
        $next = sodium_crypto_sign_publickey(sodium_crypto_sign_keypair());
        $firstId = hash('sha256', $first);
        $nextId = hash('sha256', $next);
        file_put_contents($root . '/updater/keys/release.pub', base64_encode($first));
        $config = new Config($root, $root . '/storage/app/updater/private', 'https://example.test/manifest',
            ['example.test'], $root . '/updater/keys/release.pub');
        $keys = new TrustedKeyStore($config, new StateStore($config));
        $keys->applyRotation([
            'sequence' => 2,
            'next_keys' => [[
                'public_key' => base64_encode($next),
                'activate_after_sequence' => 3,
            ]],
            'retired_keys' => [[
                'keyid' => $firstId,
                'retire_at_sequence' => 4,
            ]],
        ], $firstId);
        assertSame([$firstId], array_keys($keys->all(2)));
        assertSame([$firstId, $nextId], array_keys($keys->all(3)));
        assertSame([$nextId], array_keys($keys->all(4)));
        expectFailure(static fn () => $keys->applyRotation([
            'sequence' => 2,
            'next_keys' => [[
                'public_key' => base64_encode(sodium_crypto_sign_publickey(sodium_crypto_sign_keypair())),
                'activate_after_sequence' => 5,
            ]],
            'retired_keys' => [],
        ], $nextId));
    } finally {
        removeTree($root);
    }
};

$tests['file apply restores changed, deleted, and added files'] = static function (): void {
    $root = fixtureRoot();
    try {
        mkdir($root . '/storage/app/updater/private', 0700, true);
        mkdir($root . '/app', 0700, true);
        mkdir($root . '/updater', 0700, true);
        file_put_contents($root . '/app/a.php', 'old-a');
        file_put_contents($root . '/app/deleted.php', 'old-delete');
        file_put_contents($root . '/updater/runtime.php', 'stable-runtime');
        $oldFiles = [];
        foreach (['app/a.php', 'app/deleted.php', 'updater/runtime.php'] as $path) {
            $oldFiles[] = ['path' => $path, 'sha256' => hash_file('sha256', $root . '/' . $path),
                'size' => filesize($root . '/' . $path), 'mode' => 0644];
        }
        $old = ['schema' => 1, 'version' => '6.0.0', 'sequence' => 1, 'files' => $oldFiles];
        file_put_contents($root . '/.portaldots-manifest.json', CanonicalJson::encode($old));
        $stage = $root . '/storage/app/updater/private/stage';
        mkdir($stage . '/app', 0700, true);
        file_put_contents($stage . '/app/a.php', 'new-a');
        file_put_contents($stage . '/app/new.php', 'new-file');
        $incoming = [];
        foreach (['app/a.php', 'app/new.php'] as $path) {
            $incoming[] = ['path' => $path, 'sha256' => hash_file('sha256', $stage . '/' . $path),
                'size' => filesize($stage . '/' . $path), 'mode' => 0644];
        }
        $config = new Config($root, $root . '/storage/app/updater/private', 'https://example.test/manifest',
            ['example.test'], $root . '/missing.pub');
        $files = new FileBackup($config);
        $files->verifyInstallation($old);
        $plan = $files->plan($old, $incoming, '6.0.1', 2);
        $job = $root . '/storage/app/updater/private/job';
        mkdir($job, 0700, true);
        $context = [];
        while (!$files->backupStep($plan, $context, $job)) {
            $context = json_decode(CanonicalJson::encode($context), true, 16, JSON_THROW_ON_ERROR);
        }
        $context = [];
        while (!$files->applyStep($plan, $context, $stage)) {
            $context = json_decode(CanonicalJson::encode($context), true, 16, JSON_THROW_ON_ERROR);
        }
        assertSame('new-a', file_get_contents($root . '/app/a.php'));
        assertTrue(!file_exists($root . '/app/deleted.php'));
        $context = [];
        while (!$files->restoreStep($plan, $context, $job)) {
            $context = json_decode(CanonicalJson::encode($context), true, 16, JSON_THROW_ON_ERROR);
        }
        assertSame('old-a', file_get_contents($root . '/app/a.php'));
        assertSame('old-delete', file_get_contents($root . '/app/deleted.php'));
        assertTrue(!file_exists($root . '/app/new.php'));

        file_put_contents($root . '/public-entry.php', '<?php echo "unmanaged";');
        expectFailure(static fn () => $files->verifyInstallation($old));
    } finally {
        removeTree($root);
    }
};

if (getenv('UPDATER_MYSQL_DSN')) {
    $tests['mysql snapshot proves and restores binary null utf8 fk auto increment and no-pk rows'] = static function (): void {
        $root = fixtureRoot();
        try {
            mkdir($root . '/storage/app/updater/private', 0700, true);
            $dsn = parseDsn((string) getenv('UPDATER_MYSQL_DSN'));
            file_put_contents($root . '/.env', implode("\n", [
                'DB_CONNECTION=mysql',
                'DB_HOST=' . $dsn['host'],
                'DB_PORT=' . $dsn['port'],
                'DB_DATABASE=' . $dsn['dbname'],
                'DB_USERNAME=' . (string) getenv('UPDATER_MYSQL_USER'),
                'DB_PASSWORD=' . (string) getenv('UPDATER_MYSQL_PASSWORD'),
            ]) . "\n");
            $pdo = new PDO((string) getenv('UPDATER_MYSQL_DSN'), (string) getenv('UPDATER_MYSQL_USER'),
                (string) getenv('UPDATER_MYSQL_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            foreach ($pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()")
                ->fetchAll(PDO::FETCH_COLUMN) as $table) {
                $pdo->exec('DROP TABLE `' . str_replace('`', '``', $table) . '`');
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            $pdo->exec('CREATE TABLE parents (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL) ENGINE=InnoDB');
            $pdo->exec('CREATE TABLE samples (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, parent_id BIGINT UNSIGNED NOT NULL, nullable_value VARBINARY(255) NULL, payload BLOB NOT NULL, label VARCHAR(100) NOT NULL, CONSTRAINT samples_parent_fk FOREIGN KEY(parent_id) REFERENCES parents(id)) ENGINE=InnoDB AUTO_INCREMENT=40');
            $pdo->exec('CREATE TABLE pivot_no_pk (parent_id BIGINT UNSIGNED NOT NULL, code VARCHAR(40) NOT NULL, UNIQUE KEY pivot_unique(parent_id, code), CONSTRAINT pivot_parent_fk FOREIGN KEY(parent_id) REFERENCES parents(id)) ENGINE=InnoDB');
            $pdo->exec("INSERT INTO parents(id,name) VALUES (7,'親')");
            $insert = $pdo->prepare('INSERT INTO samples(id,parent_id,nullable_value,payload,label) VALUES (?,?,?,?,?)');
            $insert->execute([31, 7, null, "\x00\xFFbinary", '日本語']);
            $pdo->exec("INSERT INTO pivot_no_pk(parent_id,code) VALUES (7,'a'),(7,'a-2')");
            $config = new Config($root, $root . '/storage/app/updater/private', 'https://example.test/manifest',
                ['example.test'], $root . '/missing.pub');
            $backup = new DatabaseBackup($config);
            $context = [];
            $job = $root . '/job';
            mkdir($job, 0700, true);
            $iterations = 0;
            while (!$backup->backupStep($context, $job, str_repeat('a', 32))) {
                $context = json_decode(CanonicalJson::encode($context), true, 512, JSON_THROW_ON_ERROR);
                if (++$iterations > 500) {
                    throw new RuntimeException('Backup did not finish.');
                }
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->exec('DROP TABLE samples');
            $pdo->exec("UPDATE parents SET name='changed'");
            $pdo->exec('CREATE TABLE unexpected(id INT PRIMARY KEY) ENGINE=InnoDB');
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            $restore = [];
            $iterations = 0;
            while (!$backup->restoreStep($context, $restore, $job)) {
                $restore = json_decode(CanonicalJson::encode($restore), true, 512, JSON_THROW_ON_ERROR);
                if (++$iterations > 500) {
                    throw new RuntimeException('Restore did not finish.');
                }
            }
            $row = $pdo->query('SELECT nullable_value, HEX(payload) payload, label FROM samples WHERE id=31')
                ->fetch(PDO::FETCH_ASSOC);
            assertSame(null, $row['nullable_value']);
            assertSame(strtoupper(bin2hex("\x00\xFFbinary")), $row['payload']);
            assertSame('日本語', $row['label']);
            assertSame('親', $pdo->query('SELECT name FROM parents WHERE id=7')->fetchColumn());
            assertSame(2, (int) $pdo->query('SELECT COUNT(*) FROM pivot_no_pk')->fetchColumn());
            assertTrue(!in_array('unexpected', $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN), true));
            $auto = $pdo->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='samples'")->fetchColumn();
            assertTrue((int) $auto >= 40);
        } finally {
            removeTree($root);
        }
    };
}

$failed = 0;
foreach ($tests as $name => $test) {
    try {
        $test();
        fwrite(STDOUT, "ok - {$name}\n");
    } catch (Throwable $exception) {
        $failed++;
        fwrite(STDERR, "not ok - {$name}: " . get_class($exception) . ': ' . $exception->getMessage() . "\n");
    }
}
exit($failed === 0 ? 0 : 1);

/** @return array<string, mixed> */
function signedFixture(): array
{
    return [
        'schema' => 1,
        'sequence' => 2,
        'issued_at' => gmdate(DATE_ATOM, time() - 60),
        'expires_at' => gmdate(DATE_ATOM, time() + 3600),
        'target_version' => '6.0.1',
        'from_versions' => ['6.0.0'],
        'minimum_php' => '8.3.0',
        'minimum_mysql' => '8.4.0',
        'minimum_runtime' => 1,
        'artifact' => ['url' => 'https://example.test/update.zip', 'size' => 3, 'sha256' => str_repeat('a', 64)],
        'files' => [['path' => 'app/test.php', 'size' => 3, 'sha256' => str_repeat('b', 64), 'mode' => 0644]],
        'migrations' => [],
        'next_keys' => [],
        'retired_keys' => [],
    ];
}

function fixtureRoot(): string
{
    $path = sys_get_temp_dir() . '/portaldots-updater-' . bin2hex(random_bytes(8));
    mkdir($path, 0700, true);
    return $path;
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

function expectFailure(callable $callback): void
{
    try {
        $callback();
    } catch (Throwable) {
        return;
    }
    throw new RuntimeException('Expected failure did not occur.');
}

function assertTrue(bool $condition): void
{
    if (!$condition) {
        throw new RuntimeException('Assertion failed.');
    }
}

function assertSame(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

/** @return array{host: string, port: string, dbname: string} */
function parseDsn(string $dsn): array
{
    preg_match('/host=([^;]+)/', $dsn, $host);
    preg_match('/port=([^;]+)/', $dsn, $port);
    preg_match('/dbname=([^;]+)/', $dsn, $database);
    return ['host' => $host[1] ?? '127.0.0.1', 'port' => $port[1] ?? '3306', 'dbname' => $database[1] ?? ''];
}
