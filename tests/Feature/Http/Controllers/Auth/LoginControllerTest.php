<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Eloquents\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function ログインフォームが表示される()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function デフォルトでは学籍番号とメールアドレスのどちらでもログインできる()
    {
        /** @var User */
        $user = factory(User::class)->create();

        $this->post(route('login'), [
            'login_id' => $user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'));

        $this->post(route('login'), [
            'login_id' => $user->student_id,
            'password' => 'password',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_identifiersがemailのみの場合はメールアドレスでログインでき学籍番号ではログインできない()
    {
        config(['portal.auth.login_identifiers' => ['email']]);

        /** @var User */
        $user = factory(User::class)->create();

        $this->post(route('login'), [
            'login_id' => $user->student_id,
            'password' => 'password',
        ]);
        $this->assertGuest();

        $this->post(route('login'), [
            'login_id' => $user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_identifiersがemailのみの場合ログインID欄のラベルはメールアドレスになる()
    {
        config(['portal.auth.login_identifiers' => ['email']]);

        $response = $this->get(route('login'));

        $response->assertSee('メールアドレス');
        $response->assertDontSee(config('portal.student_id_name') . 'または連絡先メールアドレス');
    }
}
