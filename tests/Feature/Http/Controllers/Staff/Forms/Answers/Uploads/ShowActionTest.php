<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms\Answers\Uploads;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\User;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    private $form;
    private $answer;
    private $question;
    private $staff;

    public function setUp(): void
    {
        parent::setUp();

        // フォーム
        $this->form = factory(Form::class)->create();
        $this->question = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'type' => 'upload',
            'number_max' => 1000000000,
            'allowed_types' => 'png|jpg|jpeg|gif',
            'options' => null,
        ]);

        // 回答
        $this->answer = factory(Answer::class)->create();

        $example_file = new File(base_path('tests/TestFile.png'));
        $filename = 'testfile_' . sha1($this->answer->id . '_' . $this->question->id) . '.png';
        $this->answer_details[] = factory(AnswerDetail::class)->create([
                'answer_id' => $this->answer->id,
                'question_id' => $this->question->id,
                'answer' => 'answer_details/' . $filename,
            ]);
        Storage::putFileAs('answer_details', $example_file, $filename);

        // スタッフ
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function ダウンロードできる()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.answers.uploads.show', [
                'form' => $this->form,
                'answer' => $this->answer,
                'question' => $this->question
            ]));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function スタッフ以外はダウンロードできない()
    {
        $response = $this->actingAs(factory(User::class)->create())
            ->get(route('staff.forms.answers.uploads.show', [
                'form' => $this->form,
                'answer' => $this->answer,
                'question' => $this->question
            ]));

        $response->assertStatus(403);
    }
}
