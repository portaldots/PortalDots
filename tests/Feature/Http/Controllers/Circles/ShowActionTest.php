<?php

namespace Tests\Feature\Http\Controllers\Circles;

use App\Eloquents\Circle;
use App\Eloquents\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;
    private $mamber;
    private $circle;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->member = factory(User::class)->create();
        $this->circle = factory(Circle::class)->states('notSubmitted')->create();

        $this->circle->users()->attach([
            $this->user->id => ['is_leader' => true],
            $this->member->id => ['is_leader' => false],
        ]);

        // 受付期間内
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
    }

    /**
     * @test
     */
    public function メンバーは企画の詳細を表示できる()
    {
        $responce = $this
                    ->actingAs($this->user)
                    ->get(
                        route('circles.show', [
                            'circle' => $this->circle,
                        ])
                    );

        $responce->assertOk();
    }

    /**
     * @test
     */
    public function 未提出の場合副責任者は削除ボタンが表示される()
    {
        $responce = $this
                    ->actingAs($this->member)
                    ->get(
                        route('circles.show', [
                            'circle' => $this->circle,
                        ])
                    );

        $responce->assertOk();
        $responce->assertSee('この企画から抜ける');
    }

    /**
     * @test
     */
    public function 提出済の場合副責任者は削除ボタンが表示されない()
    {
        $this->circle->submitted_at = now();
        $this->circle->save();

        $responce = $this
                    ->actingAs($this->member)
                    ->get(
                        route('circles.show', [
                            'circle' => $this->circle,
                        ])
                    );

        $responce->assertOk();
        $responce->assertDontSee('この企画から抜ける');
    }

    /**
     * @test
     */
    public function 責任者には削除ボタンを表示しない()
    {
        $responce = $this
                    ->actingAs($this->user)
                    ->get(
                        route('circles.show', [
                            'circle' => $this->circle,
                        ])
                    );

        $responce->assertOk();
        $responce->assertDontSee('この企画から抜ける');
    }

    /**
     * @test
     */
    public function 部外者は企画詳細を表示できない()
    {
        $anotherUser = factory(User::class)->create();

        $responce = $this
                    ->actingAs($anotherUser)
                    ->get(
                        route('circles.show', [
                            'circle' => $this->circle,
                        ])
                    );

        $responce->assertStatus(403);
    }
}
