<?php

namespace Tests\Feature\Http\Controllers\Circles\Users;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
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
    }

    /**
     * @test
     */
    public function リーダーではないメンバーが自分自身を削除することができる()
    {
        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $response = $this
                    ->actingAs($this->nonLeader)
                    ->delete(
                        route('circles.users.destroy', [
                            'circle' => $this->circle,
                            'user' => $this->nonLeader,
                        ])
                    );

        $this->assertDatabaseMissing('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    /**
     * @test
     */
    public function リーダーが別のメンバーを削除する()
    {
        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $response = $this
                    ->actingAs($this->user)
                    ->delete(
                        route('circles.users.destroy', [
                            'circle' => $this->circle,
                            'user' => $this->nonLeader,
                        ])
                    );

        $this->assertDatabaseMissing('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('circles.users.index', ['circle' => $this->circle]));
    }

    /**
     * @test
     */
    public function リーダーは自分自身を削除できない()
    {
        $response = $this
                    ->actingAs($this->user)
                    ->delete(
                        route('circles.users.destroy', [
                            'circle' => $this->circle,
                            'user' => $this->user,
                        ])
                    );

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->user->id,
            'is_leader' => true,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('circles.users.index', ['circle' => $this->circle]));
    }

    /**
     * @test
     */
    public function 部外者は企画のメンバーを削除できない()
    {
        $anotherUser = factory(User::class)->create();

        $response = $this
                    ->actingAs($anotherUser)
                    ->delete(
                        route('circles.users.destroy', [
                            'circle' => $this->circle,
                            'user' => $this->nonLeader,
                        ])
                    );

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 提出済みの企画のメンバーは削除できない()
    {
        $this->circle->submitted_at = now();
        $this->circle->save();

        $response = $this
                    ->actingAs($this->user)
                    ->delete(
                        route('circles.users.destroy', [
                            'circle' => $this->circle,
                            'user' => $this->nonLeader,
                        ])
                    );

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $this->nonLeader->id,
        ]);

        $response->assertStatus(403);
    }
}
