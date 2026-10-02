<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../updater/bootstrap.php';
if (!\PortalDots\Updater\UpdateGate::enter(dirname(__DIR__))) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store');
    echo '<!doctype html><html lang="ja"><meta charset="utf-8"><title>更新中</title>'
        . '<body><h1>PortalDots を更新しています</h1><p>管理者は更新・復旧画面で進行状況を確認できます。</p>'
        . '</body></html>';
    exit;
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists(__DIR__.'/../storage/framework/maintenance.php')) {
    require __DIR__.'/../storage/framework/maintenance.php';
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
.env ファイルが作成されていない場合は作成する
*/
if (!file_exists($app->environmentPath() . '/.env')) {
    $artisan = app(Illuminate\Contracts\Console\Kernel::class);
    copy($app->environmentPath() . '/.env.production', $app->environmentPath() . '/.env');
    $artisan->call('key:generate');
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
