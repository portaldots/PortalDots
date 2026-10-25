<?php

declare(strict_types=1);

ini_set('display_errors', '0');

require dirname(__DIR__) . '/updater/bootstrap.php';

$config = \PortalDots\Updater\Config::fromEnvironment(dirname(__DIR__));
if (!\PortalDots\Updater\HttpProbe::respondIfRequested($config)) {
    try {
        (new \PortalDots\Updater\RecoveryApplication($config))->run();
    } catch (\Throwable) {
        http_response_code(503);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store');
        echo '<!doctype html><html lang="ja"><meta charset="utf-8"><title>更新復旧</title>'
            . '<body><h1>更新状態を読み取れません</h1><p>サービス停止を維持しています。'
            . '非公開領域の更新状態とサーバーの障害ログを確認してください。</p></body></html>';
    }
}
