<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;
use App\Eloquents\Answer;

class DeleteActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;
    private $answer;
    private $nonLeader;
    private $anotherUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->states('notSubmitted')->create();
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);

        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));

        // メンバー
        $this->nonLeader = factory(User::class)->create();
        $this->nonLeader->circles()->attach($this->circle->id, ['is_leader' => false]);

        // メンバーではない
        $this->anotherUser = factory(User::class)->create();
    }

    /**
     * @test
     */
    public function 参加登録未提出であればアクセスできる()
    {
        $response = $this->actingAs($this->user)
            ->get(
                route('circles.delete', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function リーダー以外のメンバーはアクセスできない()
    {
        $response = $this->actingAs($this->nonLeader)
            ->get(
                route('circles.delete', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 部外者はアクセスできない()
    {
        $response = $this->actingAs($this->anotherUser)
            ->get(
                route('circles.delete', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 提出済みの企画は削除できない()
    {
        $this->circle->submitted_at = now();
        $this->circle->save();

        $response = $this->actingAs($this->user)
            ->get(
                route('circles.delete', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(403);
    }
}
