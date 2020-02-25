<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Eloquents\User;

class HomeActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 未ログイン状態でログインフォームが表示される()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response->assertSee('学籍番号・連絡先メールアドレス');
        $response->assertSee('パスワード');
        $response->assertSee('ログインしたままにする');
        $response->assertSee('ログイン');
        $response->assertSee('ユーザー登録');
    }

    /**
     * @test
     */
    public function メール認証が未完了の時アラートが表示される()
    {
        $user = factory(User::class)->states('not_verified')->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertSee('メール認証');
        $response->assertSee('確認メールを再送');
        $response->assertSee($user->email);
        $response->assertSee($user->univemail);
    }

    /**
     * @test
     */
    public function メール認証完了済の場合はアラートを表示しない()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route('documents.index'));

        $response->assertDontSee('ログインしていません');
        $response->assertDontSee('メール認証');
        $response->assertDontSee('確認メールを再送');
    }
}
