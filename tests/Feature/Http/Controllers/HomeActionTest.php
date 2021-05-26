<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Utils\DotenvService;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;
use App\Eloquents\Page;

class HomeActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 未インストール状態の場合はインストーラが表示される()
    {
        $this->mock(DotenvService::class, function ($mock) {
            // boolean の true ではなく、文字列の 'true' である点に注意
            $mock->shouldReceive('getValue')->once()->with('APP_NOT_INSTALLED', 'false')->andReturn('true');
        });

        $response = $this->get(route('home'));
        $response->assertRedirect(route('install.index'));
    }

    /**
     * @test
     */
    public function 未ログイン状態でログインフォームが表示される()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response->assertSee('学籍番号または連絡先メールアドレス');
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
        $response->assertSee('もっと詳しく');
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
    public function 非公開のお知らせは一覧に表示されない()
    {
        // 固定されたお知らせ
        $pinnedPrivatePageTitle = 'this is a pinned private page';
        $pinnedPublicPageTitle = 'this is a pinned public page';

        // 通常のお知らせ
        $privatePageTitle = 'this is a private page';
        $publicPageTitle = 'this is a public form';

        factory(Page::class)->create([
            'title' => $pinnedPrivatePageTitle,
            'is_pinned' => true,
            'is_public' => false,
        ]);
        factory(Page::class)->create([
            'title' => $pinnedPublicPageTitle,
            'is_pinned' => true,
            'is_public' => true,
        ]);
        factory(Page::class)->create([
            'title' => $privatePageTitle,
            'is_public' => false,
        ]);
        factory(Page::class)->create([
            'title' => $publicPageTitle,
            'is_public' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertDontSee($pinnedPrivatePageTitle);
        $response->assertSee($pinnedPublicPageTitle);
        $response->assertDontSee($privatePageTitle);
        $response->assertSee($publicPageTitle);
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
        factory(Form::class)->create([
            'name' => $normalFormName
        ]);

        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle, ['is_leader' => true]);
        $response = $this->actingAs($user)->get(route('home'));

        $response->assertDontSee($customFormName);
        $response->assertSee($normalFormName);
    }
}
