<?php

declare(strict_types=1);

namespace App\Services\Install;

use App\Services\Utils\DotenvService;
use Artisan;

class RunInstallService
{
    /**
     * @var DotenvService
     */
    private $dotenvService;

    public function __construct(DotenvService $dotenvService)
    {
        $this->dotenvService = $dotenvService;
    }

    /**
     * データベースのマイグレーションなど、PortalDots の初回起動時処理を行う
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('migrate', ['--force' => true]);

        $this->dotenvService->saveKeys(['APP_NOT_INSTALLED' => 'false']);
    }

    /**
     * 未インストール状態に戻す
     *
     * 何らかのエラーが発生し、未インストール状態に戻したい時に利用する
     */
    public function rollback()
    {
        $this->dotenvService->saveKeys(['APP_NOT_INSTALLED' => 'true']);
    }
}
