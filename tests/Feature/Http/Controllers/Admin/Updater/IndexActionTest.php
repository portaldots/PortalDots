<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Admin\Updater;

use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_administrator_with_staff_verification_can_open_updater(): void
    {
        $admin = factory(User::class)->states('admin')->create();

        $this->actingAs($admin)
            ->withSession(['staff_authorized' => true])
            ->get(route('admin.updater.index'))
            ->assertOk()
            ->assertSee('PortalDots の更新');
    }

    public function test_non_administrator_cannot_open_updater_even_with_staff_verification(): void
    {
        $staff = factory(User::class)->states('staff')->create();

        $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('admin.updater.index'))
            ->assertForbidden();
    }

    public function test_demo_mode_does_not_grant_updater_access(): void
    {
        config(['portal.enable_demo_mode' => true]);
        $admin = factory(User::class)->states('admin')->create();

        $this->actingAs($admin)
            ->withSession(['staff_authorized' => true])
            ->get(route('admin.updater.index'))
            ->assertForbidden();
    }

    public function test_update_actions_require_login(): void
    {
        foreach (['check', 'start'] as $action) {
            $this->post(route('admin.updater.' . $action))
                ->assertRedirect(route('login'));
        }
    }

    public function test_update_actions_require_administrator_permission(): void
    {
        $staff = factory(User::class)->states('staff')->create();

        foreach (['check', 'start'] as $action) {
            $this->actingAs($staff)
                ->withSession(['staff_authorized' => true])
                ->post(route('admin.updater.' . $action))
                ->assertForbidden();
        }
    }

    public function test_update_actions_require_staff_verification(): void
    {
        $admin = factory(User::class)->states('admin')->create();

        foreach (['check', 'start'] as $action) {
            $this->actingAs($admin)
                ->post(route('admin.updater.' . $action))
                ->assertRedirect(route('staff.verify.index'));
        }
    }

    public function test_update_actions_reject_missing_csrf_token(): void
    {
        $admin = factory(User::class)->states('admin')->create();
        // Exercise the real middleware instead of Laravel's testing-environment bypass.
        $this->app['env'] = 'production';

        foreach (['check', 'start'] as $action) {
            $this->actingAs($admin)
                ->withSession(['staff_authorized' => true, '_token' => 'expected-token'])
                ->post(route('admin.updater.' . $action))
                ->assertStatus(419);
        }
    }
}
