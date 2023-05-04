<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Form;
use App\Eloquents\Permission;
use App\Eloquents\Question;
use App\Eloquents\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyActionTest extends TestCase
{
    use RefreshDatabase;

    private ?Form $form;
    private ?Collection $questions;
    private ?Collection $answers;
    private array $answerDetails;
    private ?User $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = factory(Form::class)->create();
        $this->questions = factory(Question::class, 2)->create([
            'form_id' => $this->form->id,
            'is_required' => false,
            'type' => 'text'
        ]);
        $this->answers = factory(Answer::class, 2)->create([
            'form_id' => $this->form->id,
        ]);
        foreach ($this->answers as $answer) {
            $this->answerDetails[] = factory(AnswerDetail::class)->create([
                'answer_id' => $answer->id,
                'question_id' => $this->questions[0]->id,
                'answer' => '回答 １'
            ]);
            $this->answerDetails[] = factory(AnswerDetail::class)->create([
                'answer_id' => $answer->id,
                'question_id' => $this->questions[1]->id,
                'answer' => '回答 ２'
            ]);
        }
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function 回答を削除できる()
    {
        Permission::create(['name' => 'staff.forms.answers.delete']);
        $this->staff->syncPermissions(['staff.forms.answers.delete']);

        $this->assertDatabaseCount('questions', 2);
        $this->assertDatabaseCount('answers', 2);
        $this->assertDatabaseCount('answer_details', 4);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(route('staff.forms.answers.destroy', [
                'form' => $this->form, 'answer' => $this->answers[0]
            ]));

        $response->assertRedirect(route('staff.forms.answers.index', ['form' => $this->form]));

        $this->assertDatabaseCount('questions', 2);
        $this->assertDatabaseCount('answers', 1);
        $this->assertDatabaseCount('answer_details', 2);
    }

    /**
     * @test
     */
    public function 権限がない場合はフォームを削除できない()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(route('staff.forms.answers.destroy', [
                'form' => $this->form, 'answer' => $this->answers[0]
            ]));

        $response->assertForbidden();
    }
}
