<?php

namespace Tests\Feature;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\DocumentVersion;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use App\Services\Navigation\MenuItem;
use App\Services\Navigation\MenuRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * config/portal.php の terms を上書きし、MenuRegistry に対して
 * remove() / add() を行った場合に、OSS側のファイルを一切変更せずに
 * 画面上の呼称・メニュー構成が変わることを検証する。
 * プライベートなデプロイ用パッケージが行う拡張を模している
 */
class TerminologyOverrideTest extends TestCase
{
    use RefreshDatabase;

    private function overrideTerms(): void
    {
        config([
            'portal.terms.circle' => '案件',
            'portal.terms.form' => '依頼',
            'portal.terms.document' => 'ファイル',
            'portal.terms.contact' => 'メッセージ',
            'portal.terms.staff_side' => '自社',
            'portal.terms.document_status_circle_pending' => '承認してください',
            'portal.terms.document_status_circle_approved' => '承認済み',
            'portal.terms.document_status_staff_pending' => '承認待ち',
            'portal.terms.document_status_staff_approved' => '承認済み',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 用語を上書きすると企画メンバー向けのナビゲーションとページに反映される()
    {
        $this->overrideTerms();

        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);

        $response = $this->selectCircle($circle)
            ->actingAs($user)
            ->get(route('documents.index'));
        $response->assertOk();

        // ナビゲーション : 配布資料→ファイル、申請→依頼、お問い合わせ→メッセージ
        $response->assertSee('<a href="' . route('documents.index') . '" class="drawer-nav__link is-active">', false);
        $response->assertSeeInOrder([
            '<a href="' . route('documents.index') . '" class="drawer-nav__link is-active">',
            'ファイル',
        ], false);
        $response->assertSeeInOrder([
            '<a href="' . route('forms.index') . '" class="drawer-nav__link">',
            '依頼',
        ], false);
        $response->assertSeeInOrder([
            '<a href="' . route('contacts') . '" class="drawer-nav__link">',
            'メッセージ',
        ], false);

        // ページタイトルにも用語が反映される
        preg_match('/<title>\s*(.*?)\s*<\/title>/s', $response->getContent(), $matches);
        $this->assertStringStartsWith('ファイル', $matches[1] ?? '');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function メニュー項目を差し替えるとOSS側のファイルを変更せずにナビゲーションが変わる()
    {
        /** @var MenuRegistry $menuRegistry */
        $menuRegistry = App::make(MenuRegistry::class);

        // お知らせを非表示にし、代わりにテンプレートへのリンクを追加する
        $menuRegistry->remove(MenuRegistry::SECTION_CIRCLE, 'pages');
        $menuRegistry->add(
            MenuRegistry::SECTION_CIRCLE,
            new MenuItem(
                key: 'templates',
                label: 'テンプレート',
                route: 'home',
                routeParams: [],
                activePattern: 'templates*',
                icon: 'fas fa-copy',
                visible: fn () => true,
            ),
            after: 'home',
        );

        $response = $this->get(route('home'));
        $response->assertOk();

        $response->assertDontSee('href="' . route('pages.index') . '"', false);
        $response->assertSeeInOrder([
            '<a href="' . route('home') . '" class="drawer-nav__link is-active">',
            'ホーム',
            'テンプレート',
            '<a href="' . route('documents.index') . '" class="drawer-nav__link">',
        ], false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 用語を上書きすると配布資料の確認状態ラベルとスタッフ進捗ページに反映される()
    {
        $this->overrideTerms();

        $staff = factory(User::class)->states('staff')->create();
        $document = factory(Document::class)->create();
        $version = factory(DocumentVersion::class)->create(['document_id' => $document->id]);
        $circle = factory(Circle::class)->create();

        $approval = DocumentApproval::create([
            'document_id' => $document->id,
            'circle_id' => $circle->id,
            'document_version_id' => $version->id,
            'status' => DocumentApproval::STATUS_PENDING,
        ]);

        $this->assertSame('承認してください', $approval->circleStatusLabel());
        $this->assertSame('承認待ち', $approval->staffStatusLabel());

        $approval->status = DocumentApproval::STATUS_APPROVED;
        $approval->save();
        $this->assertSame('承認済み', $approval->fresh()->circleStatusLabel());
        $this->assertSame('承認済み', $approval->fresh()->staffStatusLabel());

        // スタッフ進捗ページのタイトル・見出し・「誰の番」にも用語が反映される
        Permission::create(['name' => 'staff.circles.read']);
        $staff->syncPermissions(['staff.circles.read']);

        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);

        $response = $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.progress.index'));
        $response->assertOk();
        preg_match('/<title>\s*(.*?)\s*<\/title>/s', $response->getContent(), $matches);
        $this->assertStringStartsWith('案件の進捗', $matches[1] ?? '');
        $response->assertSee('自社の対応が必要');
    }
}
