<?php

namespace Tests\Feature\Http\Controllers\Staff\Verify;

use App\Eloquents\User;
use App\Services\Auth\StaffAuthService;
use App\Notifications\Auth\StaffAuthNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function スタッフ認証メールが送信される()
    {
        Notification::fake();

        /** @var User */
        $staff = factory(User::class)->state('staff')->create();

        $this->actingAs($staff)->get(route('staff.verify.index'));

        Notification::assertSentTo(
            [$staff],
            StaffAuthNotification::class
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function デモモードの場合はスタッフモードホームへリダイレクトされる()
    {
        Config::set('portal.enable_demo_mode', true);
        Notification::fake();

        /** @var User */
        $staff = factory(User::class)->state('staff')->create();

        $response = $this->actingAs($staff)->get(route('staff.verify.index'));

        $response->assertRedirect(route('staff.index'));

        // スタッフ認証メールは送信されない
        Notification::assertNotSentTo(
            [$staff],
            StaffAuthNotification::class
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function デモモードのスタッフ入口は設定したルートへリダイレクトされる()
    {
        Route::get('/staff/custom-home', fn () => 'custom home')->name('staff.custom-home');
        Route::getRoutes()->refreshNameLookups();
        Config::set('portal.navigation.staff_home_route', 'staff.custom-home');
        Config::set('portal.enable_demo_mode', true);
        $staff = factory(User::class)->state('staff')->create();

        $this->actingAs($staff)->get(route('staff.verify.index'))
            ->assertRedirect(route('staff.custom-home'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 認証完了後は既定のスタッフ入口へリダイレクトされる()
    {
        $this->verifyStaff()->assertRedirect(route('staff.index'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 認証完了後は設定したスタッフ入口へリダイレクトされる()
    {
        Route::get('/staff/custom-home', fn () => 'custom home')->name('staff.custom-home');
        Route::getRoutes()->refreshNameLookups();
        Config::set('portal.navigation.staff_home_route', 'staff.custom-home');

        $this->verifyStaff()->assertRedirect(route('staff.custom-home'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 認証完了後は保存済みの遷移先を優先する()
    {
        Route::get('/staff/custom-home', fn () => 'custom home')->name('staff.custom-home');
        Route::getRoutes()->refreshNameLookups();
        Config::set('portal.navigation.staff_home_route', 'staff.custom-home');

        $this->verifyStaff(route('staff.pages.index'))->assertRedirect(route('staff.pages.index'));
    }

    private function verifyStaff(?string $previousUrl = null)
    {
        $staff = factory(User::class)->state('staff')->create();

        return $this->actingAs($staff)
            ->withSession([
                'staff_auth_service__user_id' => $staff->id,
                'staff_auth_service__verify_code_hash' => Hash::make('123456'),
                'staff_auth_service__expired_at' => Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s'),
                StaffAuthService::SESSION_KEY_PREVIOUS_URL => $previousUrl,
            ])
            ->post(route('staff.verify.index'), ['verify_code' => '123456']);
    }
}
