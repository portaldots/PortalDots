<?php

namespace Tests\Feature\Http\Controllers\Circles;

use App\Eloquents\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();

        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
    }

    /**
     * @test
     */
    public function 説明が設定されているときは説明を表示する()
    {
        $this->participationForm->description = '注意事項';
        $this->participationForm->save();

        $responce = $this
            ->actingAs($this->user)
            ->get(
                route('circles.create')
            );

        $responce->assertOk();
        $responce->assertSee('必ずお読みください');
        $responce->assertSee('注意事項');
    }

    /**
     * @test
     */
    public function 説明が設定されていないときは説明を表示しない()
    {
        $this->participationForm->description = null;
        $this->participationForm->save();

        $responce = $this
            ->actingAs($this->user)
            ->get(
                route('circles.create')
            );

        $responce->assertOk();
        $responce->assertDontSee('必ずお読みください');
    }
}
