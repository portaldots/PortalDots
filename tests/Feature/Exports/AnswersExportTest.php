<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Exports\AnswersExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class AnswersExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Form
     */
    private $form;

    /**
     * @var AnswersExport
     */
    private $answersExport;

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var Question
     */
    private $question;

    /**
     * @var Question
     */
    private $upload_question;

    /**
     * @var Answer
     */
    private $answer;

    /**
     * @var AnswerDetail
     */
    private $detail;

    /**
     * @var AnswerDetail
     */
    private $upload_detail;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = factory(Form::class)->create();

        $this->answersExport = App::make(AnswersExport::class, ['form' => $this->form]);

        $this->circle = factory(Circle::class)->create([
            'name' => '片付けチェック見守ります',
        ]);

        $this->question = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'priority' => 2,
            'name' => 'せつもん',
        ]);

        $this->upload_question = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'priority' => 1,
            'name' => 'あっぷろーど',
            'type' => 'upload',
        ]);

        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);

        $this->detail = factory(AnswerDetail::class)->create([
            'answer_id' => $this->answer->id,
            'question_id' => $this->question->id,
        ]);

        $this->upload_detail = factory(AnswerDetail::class)->create([
            'answer_id' => $this->answer->id,
            'question_id' => $this->upload_question->id,
            'answer' => 'answer_details/TEST.png',
        ]);
    }

    /**
     * @test
     */
    public function map_回答のフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                $this->answer->id,
                $this->circle->id,
                '片付けチェック見守ります',
                'TEST.png',
                $this->detail->answer,
            ],
            $this->answersExport->map($this->answer)
        );
    }

    /**
     * @test
     */
    public function headings_設問からヘッダーが作成される()
    {
        $this->assertEquals(
            [
                '回答ID',
                '企画ID',
                '企画名',
                'あっぷろーど',
                'せつもん',
            ],
            $this->answersExport->headings()
        );
    }
}
