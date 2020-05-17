<?php

namespace Tests\Feature\Services\Install;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Jackiedo\DotenvEditor\DotenvEditor;
use App;

abstract class AbstractServiceBaseTestCase extends TestCase
{
    /**
     * @var DotenvEditor
     */
    protected $editor;

    /**
     * @var string
     */
    protected $app_not_installed_cache;

    public function setUp(): void
    {
        parent::setUp();

        $this->editor = App::make(DotenvEditor::class);

        $this->app_not_installed_cache = $this->editor->getValue('APP_NOT_INSTALLED');
        $this->editor->setKey('APP_NOT_INSTALLED', 'false');
        $this->editor->save();
    }

    public function tearDown(): void
    {
        $this->editor->setKey('APP_NOT_INSTALLED', $this->app_not_installed_cache);
        $this->editor->save();

        parent::tearDown();
    }
}
