<?php

namespace Tests\Feature\Http\Controllers\Staff;

use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class HomeActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function スタッフ認証が完了していない場合は認証ページへリダイレクトされる()
    {
        /** @var User */
        $staff = factory(User::class)->state('staff')->create();

        $response = $this->actingAs($staff)
            ->get(route('staff.index'));

        $response->assertRedirect(route('staff.verify.index'));
    }

    /**
     * @test
     */
    public function スタッフ認証が完了している場合はスタッフモードホームが表示される()
    {
        /** @var User */
        $staff = factory(User::class)->state('staff')->create();

        $response = $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.index'));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function デモモードの場合はスタッフ認証をしていなくてもスタッフモードホームが表示される()
    {
        Config::set('portal.enable_demo_mode', true);

        /** @var User */
        $staff = factory(User::class)->state('staff')->create();

        $response = $this->actingAs($staff)
            ->get(route('staff.index'));

        $response->assertOk();
    }
}
