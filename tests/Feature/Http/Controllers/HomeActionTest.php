<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Utils\DotenvService;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\DocumentVersion;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Page;
use App\Eloquents\ParticipationType;

class HomeActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 未インストール状態の場合はインストーラが表示される()
    {
        $this->mock(DotenvService::class, function ($mock) {
            // boolean の true ではなく、文字列の 'true' である点に注意
            $mock->shouldReceive('getValue')->once()->with('APP_NOT_INSTALLED', 'false')->andReturn('true');
        });

        $response = $this->get(route('home'));
        $response->assertRedirect(route('install.index'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 未ログイン状態でログイン画面への導線が表示される()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        $response->assertSee('home-header');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function メール認証が未完了の時アラートが表示される()
    {
        $user = factory(User::class)->states('not_verified')->create();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertSee('メール認証');
        $response->assertSee('もっと詳しく');
        $response->assertSee($user->email);
        $response->assertSee($user->univemail);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function メール認証完了済の場合はアラートを表示しない()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route('documents.index'));

        $response->assertDontSee('メール認証');
        $response->assertDontSee('確認メールを再送');
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function 参加登録フォームは一覧に表示されない()
    {
        $participationFormName = 'this is a registration for participation form';
        $normalFormName = 'this is a normal form';

        // 参加登録フォームを作成
        $participationForm = factory(Form::class)->create([
            'name' => $participationFormName
        ]);
        ParticipationType::factory()->create([
            'form_id' => $participationForm->id
        ]);

        // カスタムフォームではない通常のフォームも作成
        factory(Form::class)->create([
            'name' => $normalFormName
        ]);

        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle, ['is_leader' => true]);
        $response = $this->actingAs($user)->get(route('home'));

        $response->assertDontSee($participationFormName);
        $response->assertSee($normalFormName);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ゲストには対応が必要なものセクションが表示されない()
    {
        $response = $this->get(route('home'));

        $response->assertDontSee('対応が必要なもの');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 対応が必要なものが無い場合は空の表示になる()
    {
        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle, ['is_leader' => true]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertSee('対応が必要なもの');
        $response->assertSee('対応が必要なものはありません');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 対応が必要なものは選択中の企画のものだけが表示される()
    {
        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle, ['is_leader' => true]);

        // 選択中の企画向けの未提出フォーム
        factory(Form::class)->create(['requires_review' => false, 'name' => '自分の企画向けフォーム']);

        // 他の企画だけに宛てた確認依頼
        $otherCircle = factory(Circle::class)->create();
        $otherDocument = factory(Document::class)->create(['name' => '他企画向け配布資料']);
        $otherVersion = factory(DocumentVersion::class)->create(['document_id' => $otherDocument->id]);
        DocumentApproval::create([
            'document_id' => $otherDocument->id,
            'circle_id' => $otherCircle->id,
            'document_version_id' => $otherVersion->id,
            'status' => DocumentApproval::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertSee('自分の企画向けフォーム');
        $response->assertSee('提出してください');
        $response->assertDontSee('対応が必要なものはありません');

        // 「最近の配布資料」など、他のセクションにも同名の資料が出うるため、
        // 「対応が必要なもの」に渡されたデータそのものを検証する
        $progress = $response->viewData('circle_progress');
        $labels = $progress
            ->unitsInState([
                \App\Services\Circles\ValueObjects\ProgressUnit::STATE_TODO,
                \App\Services\Circles\ValueObjects\ProgressUnit::STATE_CHANGES,
            ])
            ->map(fn ($unit) => $unit->getLabel());

        $this->assertContains('自分の企画向けフォーム', $labels->all());
        $this->assertNotContains('他企画向け配布資料', $labels->all());
    }
}
