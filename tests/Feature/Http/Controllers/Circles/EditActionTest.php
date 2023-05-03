<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Answer;

class EditActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private ?User $user;
    private ?Circle $circle;
    private ?Answer $answer;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->states('notSubmitted')->create([
            'participation_type_id' => $this->participationForm->id
        ]);
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->participationForm->id,
            'circle_id' => $this->circle->id,
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);

        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
    }

    /**
     * @test
     */
    public function 責任者は企画の情報を表示できる()
    {
        $this->withoutExceptionHandling();

        $response = $this
            ->actingAs($this->user)
            ->get(
                route('circles.edit', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function 副責任者は企画の情報を表示できない()
    {
        $member = factory(User::class)->create();
        $member->circles()->attach($this->circle->id, ['is_leader' => false]);

        $responce = $this
            ->actingAs($member)
            ->get(
                route('circles.edit', [
                    'circle' => $this->circle,
                ])
            );

        $responce->assertStatus(403);
    }

    /**
     * @test
     */
    public function 部外者は企画の情報を表示できない()
    {
        $anotherUser = factory(User::class)->create();

        $response = $this
            ->actingAs($anotherUser)
            ->get(
                route('circles.edit', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 提出済みの企画の情報は表示できない()
    {
        $this->circle->submitted_at = now();
        $this->circle->save();

        $response = $this
            ->actingAs($this->user)
            ->get(
                route('circles.edit', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertStatus(403);
    }
}
