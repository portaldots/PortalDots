<?php

namespace Tests\Feature\Services\Utils;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\Utils\DotenvService;
use Illuminate\Support\Facades\App;
use Jackiedo\DotenvEditor\DotenvEditor;
use Jackiedo\DotenvEditor\Exceptions\KeyNotFoundException;

class DotenvServiceTest extends TestCase
{
    /**
     * @var DotenvService
     */
    private $dotenvService;

    public function setUp(): void
    {
        parent::setUp();
        $this->dotenvService = App::make(DotenvService::class);
    }

    /**
     * @test
     */
    public function getValue_値が存在すれば取得できる()
    {
        $this->mock(DotenvEditor::class, function ($mock) {
            $mock->shouldReceive('getValue')->once()->with('EXAMPLE_KEY')->andReturn('exampleValue');
        });

        $this->assertSame('exampleValue', $this->dotenvService->getValue('EXAMPLE_KEY'));
    }

    /**
     * @test
     */
    public function getValue_値が存在しなければデフォルト値を返す()
    {
        $this->mock(DotenvEditor::class, function ($mock) {
            $mock->shouldReceive('getValue')
                ->once()
                ->with('EXAMPLE_KEY', 'defaultValue')
                ->andThrow(new KeyNotFoundException());
        });

        $this->assertSame('defaultValue', $this->dotenvService->getValue('EXAMPLE_KEY', 'defaultValue'));
    }

    /**
     * @test
     */
    public function getValue_値が存在せずデフォルト値も未設定の場合はnullを返す()
    {
        $this->mock(DotenvEditor::class, function ($mock) {
            $mock->shouldReceive('getValue')->once()->with('EXAMPLE_KEY')->andThrow(new KeyNotFoundException());
        });

        $this->assertSame(null, $this->dotenvService->getValue('EXAMPLE_KEY'));
    }

    /**
     * @test
     */
    public function saveKeys()
    {
        $this->mock(DotenvEditor::class, function ($mock) {
            $mock->shouldReceive('setValue')->once()->with('EXAMPLE_KEY_1', 'value1');
            $mock->shouldReceive('setValue')->once()->with('EXAMPLE_KEY_2', 'value2');
            $mock->shouldReceive('setValue')->once()->with('EXAMPLE_KEY_3', 'value3');
            $mock->shouldReceive('save')->once();
        });

        $this->dotenvService->saveKeys([
            'EXAMPLE_KEY_1' => 'value1',
            'EXAMPLE_KEY_2' => 'value2',
            'EXAMPLE_KEY_3' => 'value3',
        ]);
    }
}
