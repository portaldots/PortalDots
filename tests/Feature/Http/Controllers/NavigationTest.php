<?php

namespace Tests\Feature\Http\Controllers;

use App\Eloquents\Circle;
use App\Eloquents\Page;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

/**
 * ドロワー・ボトムタブが MenuRegistry からのレンダリングに変わった後も、
 * リファクタリング前と同じ項目・順序・href・アクティブ状態・未読バッジを
 * 表示することを検証する
 */
class NavigationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function モード切替を設定で非表示にできる()
    {
        config(['portal.navigation.show_mode_switch' => false]);
        $admin = factory(User::class)->states('admin')->create();

        $this->actingAs($admin)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.index'))
            ->assertOk()
            ->assertDontSee('一般モードへ');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ゲストには基本項目のみ表示され順序とアクティブ状態が正しい()
    {
        $response = $this->get(route('home'));
        $response->assertOk();

        // ドロワー : ホーム(現在地) → お知らせ → 配布資料 → ユーザー設定(ゲスト用) の順
        $response->assertSeeInOrder([
            '<a href="' . route('home') . '" class="drawer-nav__link is-active">',
            'ホーム',
            '<a href="' . route('pages.index') . '" class="drawer-nav__link">',
            'お知らせ',
            '<a href="' . route('documents.index') . '" class="drawer-nav__link">',
            '配布資料',
            '<a href="' . route('user.appearance') . '" class="drawer-nav__link">',
            'ユーザー設定',
        ], false);

        // 未ログインでは申請・お問い合わせ・ログイン中用のユーザー設定は表示されない
        $response->assertDontSee('href="' . route('forms.index') . '"', false);
        $response->assertDontSee('href="' . route('contacts') . '"', false);
        $response->assertDontSee('href="' . route('user.edit') . '"', false);

        // ボトムタブ : ホーム(現在地) → お知らせ → 配布資料
        $response->assertSeeInOrder([
            '<a href="' . route('home') . '" class="bottom_tabs-tab is-active">',
            '<a href="' . route('pages.index') . '" class="bottom_tabs-tab">',
            '<a href="' . route('documents.index') . '" class="bottom_tabs-tab">',
        ], false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 選択中の企画がある企画メンバーには申請とお問い合わせが表示され未読バッジが出る()
    {
        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);
        factory(Page::class)->create();

        $response = $this->selectCircle($circle)
            ->actingAs($user)
            ->get(route('documents.index'));
        $response->assertOk();

        $response->assertSeeInOrder([
            '<a href="' . route('home') . '" class="drawer-nav__link">',
            '<a href="' . route('pages.index') . '" class="drawer-nav__link">',
            '<a href="' . route('documents.index') . '" class="drawer-nav__link is-active">',
            '<a href="' . route('forms.index') . '" class="drawer-nav__link">',
            '申請',
            '<a href="' . route('contacts') . '" class="drawer-nav__link">',
            'お問い合わせ',
            '<a href="' . route('user.edit') . '" class="drawer-nav__link">',
            'ユーザー設定',
        ], false);

        // 未読のお知らせが1件あるので、ドロワーには件数バッジ、ボトムタブにはドット表示が出る
        $response->assertSee(
            '<app-badge primary pill strong class="drawer-nav__badge">',
            false
        );
        preg_match('/class="drawer-nav__badge">\s*(\d+)\s*<\/app-badge>/', $response->getContent(), $matches);
        $this->assertSame('1', $matches[1] ?? null, 'ドロワーの未読バッジの件数が一致しません');
        $response->assertSee('<i class="fas fa-circle bottom_tabs-tab__notifier"></i>', false);

        // ゲスト用のユーザー設定リンクは表示されない
        $response->assertDontSee('href="' . route('user.appearance') . '"', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限を限定されたスタッフには許可された項目だけ表示される()
    {
        Permission::create(['name' => 'staff.pages.read']);
        $staff = factory(User::class)->states('staff')->create();
        $staff->syncPermissions(['staff.pages.read']);

        $response = $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.pages.index'));
        $response->assertOk();

        // staff.index のアクティブ判定パターンは 'staff' 完全一致のため、
        // staff/pages ページでは staff.index 側はアクティブにならない
        $response->assertSeeInOrder([
            '<a href="' . route('staff.index') . '" class="drawer-nav__link">',
            'スタッフモード ホーム',
            '<a href="' . route('staff.pages.index') . '" class="drawer-nav__link is-active">',
            'お知らせ管理',
        ], false);

        foreach (
            [
            'staff.users.index',
            'staff.circles.index',
            'staff.progress.index',
            'staff.tags.index',
            'staff.places.index',
            'staff.documents.index',
            'staff.forms.index',
            'staff.threads.index',
            'staff.contacts.categories.index',
            'staff.permissions.index',
            ] as $routeName
        ) {
            $response->assertDontSee('href="' . route($routeName) . '"', false);
        }

        // 管理者ではないので管理者セクションも表示されない
        $response->assertDontSee('href="' . route('admin.activity_log.index') . '"', false);
        $response->assertDontSee('href="' . route('admin.portal.edit') . '"', false);
        $response->assertDontSee('管理者');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 管理者には全てのスタッフ項目と管理者セクションが表示される()
    {
        $admin = factory(User::class)->states('admin')->create();

        $response = $this->actingAs($admin)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.index'));
        $response->assertOk();

        $response->assertSeeInOrder([
            '<a href="' . route('staff.index') . '" class="drawer-nav__link is-active">',
            'スタッフモード ホーム',
            '<a href="' . route('staff.users.index') . '" class="drawer-nav__link">',
            'ユーザー情報管理',
            '<a href="' . route('staff.circles.index') . '" class="drawer-nav__link">',
            '企画情報管理',
            '<a href="' . route('staff.progress.index') . '" class="drawer-nav__link">',
            '進捗',
            '<a href="' . route('staff.tags.index') . '" class="drawer-nav__link">',
            '企画タグ管理',
            '<a href="' . route('staff.places.index') . '" class="drawer-nav__link">',
            '場所情報管理',
            '<a href="' . route('staff.pages.index') . '" class="drawer-nav__link">',
            'お知らせ管理',
            '<a href="' . route('staff.documents.index') . '" class="drawer-nav__link">',
            '配布資料管理',
            '<a href="' . route('staff.forms.index') . '" class="drawer-nav__link">',
            '申請管理',
            '<a href="' . route('staff.threads.index') . '" class="drawer-nav__link">',
            'お問い合わせ',
            '<a href="' . route('staff.contacts.categories.index') . '" class="drawer-nav__link">',
            'お問い合わせ受付設定',
            '<a href="' . route('staff.permissions.index') . '" class="drawer-nav__link">',
            'スタッフの権限設定',
            '<a href="' . route('admin.activity_log.index') . '" class="drawer-nav__link">',
            'アクティビティログ',
            '<a href="' . route('admin.portal.edit') . '" class="drawer-nav__link">',
            'PortalDots の設定',
        ], false);

        // ドロワーの管理者セクション2項目・ログイン中ユーザー情報1箇所・
        // スタッフモードホームの管理者向けショートカット2項目、計5箇所に「管理者」バッジが付く
        $this->assertSame(
            5,
            substr_count($response->getContent(), '<app-badge danger>管理者</app-badge>')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 既定のスタッフ入口はstaff_indexを指す()
    {
        $this->withoutVite();
        $this->assertSame('staff.index', config('portal.navigation.staff_home_route'));
        $staff = factory(User::class)->states('staff')->create();

        $staffPage = $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.pages.index'));
        $staffPage->assertSee('<a class="drawer-header" href="' . route('staff.index') . '">', false);

        $circlePage = $this->get(route('home'));
        $circlePage->assertSee('<a href="' . route('staff.index') . '" class="btn is-primary is-block">', false);

        $noDrawerPage = $this->get(route('staff.about'));
        $noDrawerPage->assertSee('href="' . route('staff.index') . '"', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function スタッフ入口ルートの差し替えが各リンクに反映される()
    {
        $this->withoutVite();
        Route::get('/staff/custom-home', fn () => 'custom home')->name('staff.custom-home');
        Route::getRoutes()->refreshNameLookups();
        config(['portal.navigation.staff_home_route' => 'staff.custom-home']);
        $staff = factory(User::class)->states('staff')->create();
        $destination = route('staff.custom-home');

        $staffPage = $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.pages.index'));
        $staffPage->assertSee('<a class="drawer-header" href="' . $destination . '">', false);
        $staffPage->assertSee('<a href="' . $destination . '" class="drawer-nav__link">', false);

        $circlePage = $this->get(route('home'));
        $circlePage->assertSee('<a href="' . $destination . '" class="btn is-primary is-block">', false);

        $noDrawerPage = $this->get(route('staff.about'));
        $noDrawerPage->assertSee('href="' . $destination . '"', false);
    }
}
