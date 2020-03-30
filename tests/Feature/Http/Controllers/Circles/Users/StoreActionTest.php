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

class StoreActionTest extends BaseTestCase
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
    }

    /**
     * @test
     */
    public function 正しいトークンであれば招待を受け入れることができる()
    {
        $invitedUser = factory(User::class)->create();

        $this->assertDatabaseMissing('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $invitedUser->id,
        ]);

        $response = $this
                    ->actingAs($invitedUser)
                    ->post(
                        route('circles.users.store', [
                            'circle' => $this->circle,
                        ]),
                        [
                            'invitation_token' => $this->circle->invitation_token,
                        ]
                    );

        $this->assertDatabaseHas('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $invitedUser->id,
            'is_leader' => false,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('circles.users.index', ['circle' => $this->circle]));
    }

    /**
     * @test
     */
    public function 間違ったトークンでは企画のメンバーになれない()
    {
        $invitedUser = factory(User::class)->create();

        $response = $this
                    ->actingAs($invitedUser)
                    ->post(
                        route('circles.users.store', [
                            'circle' => $this->circle,
                        ]),
                        [
                            'invitation_token' => 'INVALID_WRONG_TOKEN',
                        ]
                    );

        $this->assertDatabaseMissing('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $invitedUser->id,
        ]);

        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function 提出済みの企画の招待は受け入れることができない()
    {
        $this->circle->submitted_at = now();
        $this->circle->save();

        $invitedUser = factory(User::class)->create();

        $response = $this
                    ->actingAs($invitedUser)
                    ->post(
                        route('circles.users.store', [
                            'circle' => $this->circle,
                        ]),
                        [
                            'invitation_token' => $this->circle->invitation_token,
                        ]
                    );

        $this->assertDatabaseMissing('circle_user', [
            'circle_id' => $this->circle->id,
            'user_id' => $invitedUser->id,
        ]);

        $response->assertStatus(404);
    }
}
