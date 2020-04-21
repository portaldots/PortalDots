<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers\Uploads;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowActionTest extends TestCase
{
    /**
     * @test
     */
    public function 自分が所属していない企画によるアップロードファイルはダウンロードできない()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
