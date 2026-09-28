<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function デフォルトではユーザー登録フォームが表示される()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('users.register');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function デフォルトではログイン画面にユーザー登録への導線がある()
    {
        $response = $this->get(route('login'));

        $response->assertSee(route('register'), false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_enabledがfalseの場合ユーザー登録ルートは404になる()
    {
        config(['portal.registration.enabled' => false]);

        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_enabledがfalseの場合ログイン画面にユーザー登録への導線がない()
    {
        config(['portal.registration.enabled' => false]);

        $response = $this->get(route('login'));

        $response->assertDontSee('新規ユーザー登録');
    }
}
