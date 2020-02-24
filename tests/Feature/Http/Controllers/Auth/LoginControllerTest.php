<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginControllerTest extends TestCase
{
    /**
     * @test
     */
    public function ログインフォームが表示される()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('v2.auth.login');
    }
}
