<?php

namespace Tests\Feature\Http\Controllers\Install;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Jackiedo\DotenvEditor\DotenvEditor;

class HomeActionTest extends TestCase
{
    /**
     * @test
     */
    public function インストール済の場合はアクセスできない()
    {
        $this->mock(DotenvEditor::class, function ($mock) {
            $mock->shouldReceive('keyExists')->once()->with('APP_NOT_INSTALLED')->andReturn(true);
            // boolean の true ではなく、文字列の 'true' である点に注意
            $mock->shouldReceive('getValue')->once()->with('APP_NOT_INSTALLED')->andReturn('false');
        });

        $response = $this->get(route('install.index'));
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function 未インストール状態の場合はアクセスできる()
    {
        $this->mock(DotenvEditor::class, function ($mock) {
            $mock->shouldReceive('keyExists')->once()->with('APP_NOT_INSTALLED')->andReturn(true);
            // boolean の true ではなく、文字列の 'true' である点に注意
            $mock->shouldReceive('getValue')->once()->with('APP_NOT_INSTALLED')->andReturn('true');
        });

        $response = $this->get(route('install.index'));
        $response->assertStatus(200);
    }
}
