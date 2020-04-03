<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Answer;

class EditActionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();
        $this->form = factory(Form::class)->create([
            'open_at' => new CarbonImmutable('2020-01-26 11:42:51'),
            'close_at' => new CarbonImmutable('2020-03-26 15:23:31'),
        ]);
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
    }

    /**
     * @test
     * @dataProvider 受付期間中かどうかに応じて表示が切り替わる_provider
     */
    public function 受付期間中かどうかに応じて表示が切り替わる(
        CarbonImmutable $today,
        bool $is_answerable
    ) {
        Carbon::setTestNow($today);
        CarbonImmutable::setTestNow($today);

        $response = $this
                    ->actingAs($this->user)
                    ->get(
                        route('forms.answers.edit', [
                            'form' => $this->form,
                            'answer' => $this->answer
                        ])
                    );

        $response->assertStatus(200);

        if ($is_answerable) {
            $response->assertDontSee('受付期間外');
        } else {
            $response->assertSee('受付期間外');
        }
    }

    public function 受付期間中かどうかに応じて表示が切り替わる_provider()
    {
        return [
            '受付開始はまだまだ先' => [new CarbonImmutable('2019-12-25 23:42:22'), false],
            '受付開始前' => [new CarbonImmutable('2020-01-26 11:42:50'), false],
            '受付開始した瞬間' => [new CarbonImmutable('2020-01-26 11:42:51'), true],
            '受付期間中' => [new CarbonImmutable('2020-02-16 02:25:15'), true],
            '受付終了する瞬間' => [new CarbonImmutable('2020-03-26 15:23:31'), true],
            '受付終了後' => [new CarbonImmutable('2020-03-26 15:23:32'), false],
            '受付終了してだいぶ経過' => [new CarbonImmutable('2020-08-14 02:35:31'), false],
        ];
    }

    /**
     * @test
     */
    public function 自分が所属していない企画の回答にはアクセスできない()
    {
        $anotherUser = factory(User::class)->create();
        $anotherCircle = factory(Circle::class)->create();
        $anotherUser->circles()->attach($anotherCircle->id, ['is_leader' => true]);

        $response = $this
                    ->actingAs($anotherUser)
                    ->get(
                        route('forms.answers.edit', [
                            'form' => $this->form,
                            'answer' => $this->answer
                        ])
                    );

        $response->assertStatus(403);
    }

    /**
     * @test
     */
    public function 非公開のフォームにはアクセスできない()
    {
        $privateForm = factory(Form::class)->states('private')->create();
        $answerOfPrivateForm = factory(Answer::class)->create([
            'form_id' => $privateForm,
            'circle_id' => $this->circle->id,
        ]);

        $response = $this
                    ->actingAs($this->user)
                    ->get(
                        route('forms.answers.edit', [
                            'form' => $privateForm,
                            'answer' => $answerOfPrivateForm,
                        ])
                    );

        $response->assertStatus(404);
    }
}
