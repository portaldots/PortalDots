<?php

namespace Tests\Feature\Services\Utils;

use App;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\Utils\ReleaseInfoService;
use App\Services\Utils\ValueObjects\Version;

class ReleaseInfoServiceTest extends TestCase
{
    /**
    * @var ReleaseInfoService
    */
    private $releaseInfoService;

    public function setUp(): void
    {
        parent::setUp();
        $this->releaseInfoService = App::make(ReleaseInfoService::class);
    }

    public function versionProvider()
    {
        return [
            ['1.0.0', new Version(1, 0, 0)],
            ['0.1.2', new Version(0, 1, 2)],
            ['1.0', null],
            ['.0.', null],
            ['..', null],
            ['.', null],
            ['12345', null],
        ];
    }

    /**
     * @test
     * @dataProvider versionProvider
     */
    public function version(string $input, ?Version $expected)
    {
        if ($expected === null) {
            $this->assertNull($this->releaseInfoService->version($input));
        } else {
            $this->assertTrue($expected->equals($this->releaseInfoService->version($input)));
        }
    }
}
