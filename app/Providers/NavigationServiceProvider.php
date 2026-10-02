<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Circles\SelectorService;
use App\Services\Navigation\MenuItem;
use App\Services\Navigation\MenuRegistry;
use App\Services\Pages\ReadsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * ドロワー・ボトムタブの初期メニュー項目を登録する。
 * プライベートなデプロイ用パッケージは、この後に実行される自身の
 * ServiceProvider から MenuRegistry::add() / remove() を呼ぶことで、
 * ここで登録した項目を変更できる
 */
class NavigationServiceProvider extends ServiceProvider
{
    public $singletons = [
        MenuRegistry::class => MenuRegistry::class,
    ];

    public function boot(MenuRegistry $menuRegistry): void
    {
        $this->registerCircleItems($menuRegistry);
        $this->registerStaffItems($menuRegistry);
        $this->registerAdminItems($menuRegistry);
    }

    private function registerCircleItems(MenuRegistry $menuRegistry): void
    {
        $alwaysVisible = fn () => true;

        $menuRegistry->add(MenuRegistry::SECTION_CIRCLE, new MenuItem(
            key: 'home',
            label: 'ホーム',
            route: 'home',
            routeParams: [],
            activePattern: '/',
            icon: 'fas fa-home',
            visible: $alwaysVisible,
            showInBottomTabs: true,
        ));

        $menuRegistry->add(MenuRegistry::SECTION_CIRCLE, new MenuItem(
            key: 'pages',
            label: 'お知らせ',
            route: 'pages.index',
            routeParams: [],
            activePattern: 'pages*',
            icon: 'fas fa-bullhorn',
            visible: $alwaysVisible,
            showInBottomTabs: true,
            badge: fn () => app(ReadsService::class)->getUnreadsCountOnSelectedCircle(),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_CIRCLE, new MenuItem(
            key: 'documents',
            label: fn () => term('document'),
            route: 'documents.index',
            routeParams: [],
            activePattern: 'documents*',
            icon: 'far fa-file-alt',
            visible: $alwaysVisible,
            showInBottomTabs: true,
        ));

        $menuRegistry->add(MenuRegistry::SECTION_CIRCLE, new MenuItem(
            key: 'forms',
            label: fn () => term('form'),
            route: 'forms.index',
            routeParams: [],
            activePattern: 'forms*',
            icon: 'far fa-edit',
            visible: fn () => Auth::check() && !empty(app(SelectorService::class)->getCircle()),
            showInBottomTabs: true,
        ));

        $menuRegistry->add(MenuRegistry::SECTION_CIRCLE, new MenuItem(
            key: 'contacts',
            label: fn () => term('contact'),
            route: 'contacts',
            routeParams: [],
            activePattern: 'contacts*',
            icon: 'far fa-envelope',
            visible: fn () => Auth::check(),
            showInBottomTabs: true,
        ));

        $menuRegistry->add(MenuRegistry::SECTION_CIRCLE, new MenuItem(
            key: 'user_settings',
            label: 'ユーザー設定',
            route: 'user.edit',
            routeParams: [],
            activePattern: 'user*',
            icon: 'fas fa-cog',
            visible: fn () => Auth::check(),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_CIRCLE, new MenuItem(
            key: 'user_settings_guest',
            label: 'ユーザー設定',
            route: 'user.appearance',
            routeParams: [],
            activePattern: 'user/appearance',
            icon: 'fas fa-cog',
            visible: fn () => !Auth::check(),
        ));
    }

    private function registerStaffItems(MenuRegistry $menuRegistry): void
    {
        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_home',
            label: 'スタッフモード ホーム',
            route: fn () => config('portal.navigation.staff_home_route'),
            routeParams: [],
            activePattern: 'staff',
            icon: 'fas fa-home',
            visible: fn () => true,
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_users',
            label: 'ユーザー情報管理',
            route: 'staff.users.index',
            routeParams: [],
            activePattern: 'staff/users*',
            icon: 'far fa-address-book',
            visible: fn () => Gate::allows('staff.users.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_circles',
            label: fn () => term('circle') . '情報管理',
            route: 'staff.circles.index',
            routeParams: [],
            activePattern: 'staff/circles*',
            icon: 'fas fa-star',
            visible: fn () => Gate::allows('staff.circles.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_progress',
            label: '進捗',
            route: 'staff.progress.index',
            routeParams: [],
            activePattern: 'staff/progress*',
            icon: 'far fa-check-square',
            visible: fn () => Gate::allows('staff.circles.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_tags',
            label: fn () => term('circle') . 'タグ管理',
            route: 'staff.tags.index',
            routeParams: [],
            activePattern: 'staff/tags*',
            icon: 'fas fa-tags',
            visible: fn () => Gate::allows('staff.tags.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_places',
            label: '場所情報管理',
            route: 'staff.places.index',
            routeParams: [],
            activePattern: 'staff/places*',
            icon: 'fas fa-store',
            visible: fn () => Gate::allows('staff.places.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_pages',
            label: 'お知らせ管理',
            route: 'staff.pages.index',
            routeParams: [],
            activePattern: 'staff/pages*',
            icon: 'fas fa-bullhorn',
            visible: fn () => Gate::allows('staff.pages.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_documents',
            label: fn () => term('document') . '管理',
            route: 'staff.documents.index',
            routeParams: [],
            activePattern: 'staff/documents*',
            icon: 'far fa-file-alt',
            visible: fn () => Gate::allows('staff.documents.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_forms',
            label: fn () => term('form') . '管理',
            route: 'staff.forms.index',
            routeParams: [],
            activePattern: 'staff/forms*',
            icon: 'far fa-edit',
            visible: fn () => Gate::allows('staff.forms.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_threads',
            label: fn () => term('contact'),
            route: 'staff.threads.index',
            routeParams: [],
            activePattern: 'staff/threads*',
            icon: 'far fa-comments',
            visible: fn () => Gate::allows('staff.threads.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_contacts_categories',
            label: fn () => term('contact') . '受付設定',
            route: 'staff.contacts.categories.index',
            routeParams: [],
            activePattern: 'staff/contacts/categories*',
            icon: 'fas fa-at',
            visible: fn () => Gate::allows('staff.contacts.categories.read'),
        ));

        $menuRegistry->add(MenuRegistry::SECTION_STAFF, new MenuItem(
            key: 'staff_permissions',
            label: fn () => term('staff_side') . 'の権限設定',
            route: 'staff.permissions.index',
            routeParams: [],
            activePattern: 'staff/permissions*',
            icon: 'fas fa-key',
            visible: fn () => Gate::allows('staff.permissions.read'),
        ));
    }

    private function registerAdminItems(MenuRegistry $menuRegistry): void
    {
        $visible = fn () => Auth::check() && Auth::user()->is_admin;

        $menuRegistry->add(MenuRegistry::SECTION_ADMIN, new MenuItem(
            key: 'admin_activity_log',
            label: 'アクティビティログ',
            route: 'admin.activity_log.index',
            routeParams: [],
            activePattern: 'admin/activity_log*',
            icon: 'fas fa-user-edit',
            visible: $visible,
        ));

        $menuRegistry->add(MenuRegistry::SECTION_ADMIN, new MenuItem(
            key: 'admin_portal_settings',
            label: 'PortalDots の設定',
            route: 'admin.portal.edit',
            routeParams: [],
            activePattern: 'admin/portal*',
            icon: 'fas fa-cog',
            visible: $visible,
        ));
    }
}
