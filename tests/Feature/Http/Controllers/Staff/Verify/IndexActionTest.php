<?php

namespace Tests\Feature\Http\Controllers\Staff\Verify;

use App\Eloquents\User;
use App\Notifications\Auth\StaffAuthNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
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

    /**
     * @test
     */
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
}
