<?php

namespace Tests\Feature\Http\Controllers\Circles;

use App\Eloquents\Circle;
use App\Eloquents\Place;
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

    private ?User $user;
    private ?User $member;
    private ?Circle $circle;
    private ?Circle $notSubmittedCircle;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->member = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create([
            'participation_type_id' => $this->participationType->id
        ]);

        $this->notSubmittedCircle = factory(Circle::class)->states('notSubmitted')->create([
            'participation_type_id' => $this->participationType->id
        ]);

        $this->circle->users()->attach([
            $this->user->id => ['is_leader' => true],
            $this->member->id => ['is_leader' => false],
        ]);

        $this->notSubmittedCircle->users()->attach([
            $this->user->id => ['is_leader' => true],
            $this->member->id => ['is_leader' => false],
        ]);

        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
    }

    /**
     * @test
     */
    public function 提出済み企画の場合で未認証ユーザーには認証ページを表示する()
    {
        $response = $this->actingAs($this->user)
            ->get(
                route('circles.show', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(302);
        $response->assertRedirect(
            route('circles.auth', [
                'circle' => $this->circle
            ])
        );
    }

    /**
     * @test
     */
    public function 未提出の企画の場合は認証画面を表示しない()
    {
        $response = $this->actingAs($this->user)
            ->get(
                route('circles.show', [
                    'circle' => $this->notSubmittedCircle,
                ])
            );

        $response->assertOk();
    }

    /**
     * @test
     */
    public function メンバーは企画の詳細を表示できる()
    {
        $response = $this
            ->actingAs($this->user)
            ->withSession(['user_reauthorized_at' => now()])
            ->get(
                route('circles.show', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertOk();
    }

    /**
     * @test
     */
    public function 未提出の場合副責任者は削除ボタンが表示される()
    {
        $response = $this
            ->actingAs($this->member)
            ->get(
                route('circles.show', [
                    'circle' => $this->notSubmittedCircle,
                ])
            );

        $response->assertOk();
        $response->assertSee('この企画から抜ける');
    }

    /**
     * @test
     */
    public function 提出済の場合副責任者は削除ボタンが表示されない()
    {
        $this->circle->submitted_at = now();
        $this->circle->save();

        $response = $this
            ->actingAs($this->member)
            ->withSession(['user_reauthorized_at' => now()])
            ->get(
                route('circles.show', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertOk();
        $response->assertDontSee('この企画から抜ける');
    }

    /**
     * @test
     */
    public function 責任者には削除ボタンを表示しない()
    {
        $response = $this
            ->actingAs($this->user)
            ->get(
                route('circles.show', [
                    'circle' => $this->notSubmittedCircle,
                ])
            );

        $response->assertOk();
        $response->assertDontSee('この企画から抜ける');
    }

    /**
     * @test
     */
    public function 部外者は企画詳細を表示できない()
    {
        $anotherUser = factory(User::class)->create();

        $response = $this
            ->actingAs($anotherUser)
            ->withSession(['user_reauthorized_at' => now()])
            ->get(
                route('circles.show', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 使用場所が表示される()
    {
        $place = factory(Place::class)->create();
        $this->circle->places()->attach($place->id);

        $response = $this
            ->actingAs($this->user)
            ->withSession(['user_reauthorized_at' => now()])
            ->get(
                route('circles.show', [
                    'circle' => $this->circle,
                ])
            );
        $response->assertOk();
        $response->assertSee('使用場所');
        $response->assertSee($place->name);
    }

    /**
     * @test
     */
    public function 場所が登録されていないときは使用場所を表示しない()
    {
        $response = $this
            ->actingAs($this->user)
            ->withSession(['user_reauthorized_at' => now()])
            ->get(
                route('circles.show', [
                    'circle' => $this->circle,
                ])
            );
        $response->assertOk();
        $response->assertDontSee('使用場所');
    }
}
