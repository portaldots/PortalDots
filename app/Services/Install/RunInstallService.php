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

    public function run()
    {
        Artisan::call('migrate', ['--force' => true]);

        $this->editor->deleteKey('APP_NOT_INSTALLED');
        $this->editor->save();
    }
}
