<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Exports\AnswersExport;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $staff;

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var Question
     */
    private $question;

    /**
     * @var Answer
     */
    private $answer;

    /**
     * @var detail
     */
    private $detail;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));
        CarbonImmutable::setTestNow(new CarbonImmutable('2021-09-14 21:22:23'));

        $this->staff = factory(User::class)->states('staff')->create();

        $this->circle = factory(Circle::class)->create();

        $this->form = factory(Form::class)->create();
        $this->question = factory(Question::class)->create([
            'form_id' => $this->form->id,
        ]);
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);
        $this->detail = factory(AnswerDetail::class)->create([
            'answer_id' => $this->answer->id,
        ]);
    }

    /**
     * @test
     */
    public function 回答をCSVでダウンロードできる()
    {
        Excel::fake();
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.answers.export', ['form' => $this->form]));

        $now = now()->format('Y-m-d_H-i-s');

        Excel::assertDownloaded("form_{$this->form->id}_{$now}.csv", function (AnswersExport $export) {
            return $export->collection()->first()->circle->name === $this->circle->name
                && $export->collection()->first()->details->contains('answer', $this->detail->answer);
        });
    }
}
