<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;

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

    /**
     * @test
     */
    public function カスタムフォームは一覧に表示されない()
    {
        $customFormName = 'this is custom form';
        $normalFormName = 'this is normal form';

        // カスタムフォームを作成
        $form = factory(Form::class)->create([
            'name' => $customFormName
        ]);
        $customForm = factory(CustomForm::class)->create([
            'type' => 'circle',
            'form_id' => $form->id,
        ]);

        CustomForm::noCacheForm();

        // カスタムフォームではない通常のフォームも作成
        $normalForm = factory(Form::class)->create([
            'name' => $normalFormName
        ]);

        $response = $this->get(route('home'));

        $response->assertDontSee($customFormName);
        $response->assertSee($normalFormName);
    }
}
