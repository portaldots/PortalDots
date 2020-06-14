<?php

namespace Tests\Feature\Services\Utils;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\Utils\FormatTextService;
use Illuminate\Support\Facades\App;

class FormatTextServiceTest extends TestCase
{
    /**
     * @var FormatTextService
     */
    private $formatTextService;

    public function setUp(): void
    {
        parent::setUp();
        $this->formatTextService = App::make(FormatTextService::class);
    }

    public function filesizeProvider()
    {
        return [
            [1000, '0.98KB'],
            [1030, '1.01KB'],
            [1000000000000000, '931322.57GB'],
        ];
    }

    /**
     * @test
     * @dataProvider filesizeProvider
     */
    public function filesize($arg, $result)
    {
        $this->assertSame($result, $this->formatTextService->filesize($arg));
    }
}
