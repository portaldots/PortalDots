<?php

declare(strict_types=1);

namespace App\Services\Install;

use Jackiedo\DotenvEditor\DotenvEditor;
use Artisan;

class RunInstallService
{
    /**
     * @var DotenvEditor
     */
    private $editor;

    public function __construct(DotenvEditor $editor)
    {
        $this->editor = $editor;
    }

    /**
     * データベースのマイグレーションなど、PortalDots の初回起動時処理を行う
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('migrate', ['--force' => true]);

        $this->editor->setKey('APP_NOT_INSTALLED', 'false');
        $this->editor->save();
    }

    /**
     * 未インストール状態に戻す
     *
     * 何らかのエラーが発生し、未インストール状態に戻したい時に利用する
     */
    public function rollback()
    {
        $this->editor->setKey('APP_NOT_INSTALLED', 'true');
    }
}
