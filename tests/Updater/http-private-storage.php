#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/updater/bootstrap.php';

use PortalDots\Updater\Config;
use PortalDots\Updater\HttpProbe;
use PortalDots\Updater\PrivateStorage;

$sourceRoot = dirname(__DIR__, 2);
$failures = 0;
foreach (['public-docroot' => true, 'root-rewrite' => true, 'root-leak' => false] as $mode => $expected) {
    $root = sys_get_temp_dir() . '/portaldots-http-probe-' . bin2hex(random_bytes(8));
    mkdir($root . '/public', 0700, true);
    mkdir($root . '/storage/app/updater/private', 0700, true);
    symlink($sourceRoot . '/updater', $root . '/updater');
    copy($sourceRoot . '/public/updater.php', $root . '/public/updater.php');
    PrivateStorage::ensure($root . '/storage/app/updater/private');
    [$port, $socket] = reservePort();
    fclose($socket);
    $router = $root . '/router.php';
    if ($mode !== 'public-docroot') {
        file_put_contents($router, routerSource($root, $mode === 'root-leak'));
    }
    $log = $root . '/server.log';
    $command = [PHP_BINARY, '-S', '127.0.0.1:' . $port];
    if ($mode === 'public-docroot') {
        array_push($command, '-t', $root . '/public');
    } else {
        array_push($command, '-t', $root, $router);
    }
    $process = proc_open($command, [
        0 => ['file', '/dev/null', 'r'],
        1 => ['file', $log, 'a'],
        2 => ['file', $log, 'a'],
    ], $pipes);
    try {
        if (!is_resource($process)) {
            throw new RuntimeException('PHP test server could not be started.');
        }
        waitForServer($port);
        $_SERVER['HTTP_HOST'] = '127.0.0.1:' . $port;
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['SERVER_PORT'] = $port;
        $config = new Config(
            $root,
            $root . '/storage/app/updater/private',
            'https://example.test/manifest',
            ['example.test'],
            $root . '/updater/keys/release.pub',
            applicationUrl: 'http://127.0.0.1:' . $port,
            allowInsecureLocalhost: true,
        );
        $succeeded = true;
        try {
            (new HttpProbe($config))->verifyPrivateStorage();
        } catch (Throwable) {
            $succeeded = false;
        }
        if ($succeeded !== $expected) {
            throw new RuntimeException("Unexpected HTTP privacy result for {$mode}.");
        }
        fwrite(STDOUT, "ok - {$mode} private storage probe\n");
    } catch (Throwable $exception) {
        $failures++;
        fwrite(STDERR, "not ok - {$mode}: {$exception->getMessage()}\n");
    } finally {
        if (is_resource($process)) {
            proc_terminate($process);
            proc_close($process);
        }
        removeTree($root);
    }
}
exit($failures === 0 ? 0 : 1);

/** @return array{int, resource} */
function reservePort(): array
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
    if ($socket === false) {
        throw new RuntimeException("Cannot reserve port: {$errorCode} {$errorMessage}");
    }
    $name = stream_socket_get_name($socket, false);
    return [(int) substr((string) $name, strrpos((string) $name, ':') + 1), $socket];
}

function waitForServer(int $port): void
{
    $deadline = microtime(true) + 5;
    do {
        $socket = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.1);
        if (is_resource($socket)) {
            fclose($socket);
            return;
        }
        usleep(50_000);
    } while (microtime(true) < $deadline);
    throw new RuntimeException("PHP test server did not start: {$errorCode} {$errorMessage}");
}

function routerSource(string $root, bool $leak): string
{
    return '<?php' . "\n"
        . '$path = rawurldecode((string) parse_url($_SERVER[\'REQUEST_URI\'], PHP_URL_PATH));' . "\n"
        . 'if ($path === \'/updater.php\') { require ' . var_export($root . '/public/updater.php', true) . '; return; }' . "\n"
        . ($leak
            ? '$candidate = ' . var_export($root, true) . ' . $path;' . "\n"
            : '$candidate = ' . var_export($root . '/public', true) . ' . $path;' . "\n")
        . 'if (is_file($candidate)) { return false; }' . "\n"
        . 'http_response_code(404); echo "Not Found\\n";' . "\n";
}

function removeTree(string $path): void
{
    if (!is_dir($path)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($iterator as $item) {
        $item->isDir() && !$item->isLink() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($path);
}
