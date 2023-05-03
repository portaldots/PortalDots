<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Question;

class EditActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private ?User $user;
    private ?Circle $circle;
    private ?Question $question;
    private ?Answer $answer;

    public function setUp(): void
    {
        parent::setUp();

        // 企画参加登録と関係ない企画を作成しておく
        // （全く関係のない別の企画による回答が表示されてしまう不具合が過去に発生したため、その再発防止）
        $anotherUser = factory(User::class)->create();
        $anotherCircle =
            factory(Circle::class)->states('notSubmitted')->create([
                'participation_type_id' => $this->participationType->id
            ]);
        factory(Answer::class)->create([
            'form_id' => $this->participationForm->id,
            'circle_id' => $anotherCircle->id,
        ]);
        $anotherUser->circles()->attach($anotherCircle->id, ['is_leader' => true]);

        // テストで利用する回答
        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->states('notSubmitted')->create([
            'participation_type_id' => $this->participationType->id
        ]);
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->participationForm->id,
            'circle_id' => $this->circle->id,
        ]);
        $this->question = factory(Question::class)->create([
            'form_id' => $this->participationForm->id,
            'name' => '参加登録フォームの設問',
            'type' => 'text',
        ]);
        factory(AnswerDetail::class)->create([
            'answer_id' => $this->answer->id,
            'question_id' => $this->question->id,
            'answer' => 'これが回答です'
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
        $response->assertSee(json_encode('これが回答です'));
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
