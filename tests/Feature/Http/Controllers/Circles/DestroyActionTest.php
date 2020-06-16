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

class DestroyActionTest extends BaseTestCase
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
        Carbon::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2020-02-16 02:25:15'));

        // メンバー
        $this->nonLeader = factory(User::class)->create();
        $this->nonLeader->circles()->attach($this->circle->id, ['is_leader' => false]);

        // メンバーではない
        $this->anotherUser = factory(User::class)->create();
    }

    /**
     * @test
     */
    public function 参加登録未提出の企画を削除できる()
    {
        $this->assertDatabaseHas('circles', [
            'id' => $this->circle->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $this->assertDatabaseHas('answers', [
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(
                route('circles.destroy', [
                    'circle' => $this->circle,
                ])
            );

        $this->assertDatabaseMissing('circles', [
            'id' => $this->circle->id,
        ]);

        $this->assertDatabaseMissing('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseMissing('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $this->assertDatabaseMissing('answers', [
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    /**
     * @test
     */
    public function リーダー以外のメンバーは企画を削除できない()
    {
        $response = $this->actingAs($this->nonLeader)
            ->delete(
                route('circles.destroy', [
                    'circle' => $this->circle,
                ])
            );

        $this->assertDatabaseHas('circles', [
            'id' => $this->circle->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 部外者は企画を削除できない()
    {
        $response = $this->actingAs($this->anotherUser)
            ->delete(
                route('circles.destroy', [
                    'circle' => $this->circle,
                ])
            );

        $this->assertDatabaseHas('circles', [
            'id' => $this->circle->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

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
            ->delete(
                route('circles.destroy', [
                    'circle' => $this->circle,
                ])
            );

        $this->assertDatabaseHas('circles', [
            'id' => $this->circle->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $response->assertStatus(403);
    }
}
