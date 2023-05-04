<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

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
        $this->form = factory(Form::class)->create([
            'name' => '削除対象のフォーム'
        ]);
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
    public function フォームを削除できる()
    {
        Permission::create(['name' => 'staff.forms.delete']);
        $this->staff->syncPermissions(['staff.forms.delete']);

        $this->assertDatabaseHas('forms', [
            'name' => '削除対象のフォーム'
        ]);
        $this->assertDatabaseCount('questions', 2);
        $this->assertDatabaseCount('answers', 2);
        $this->assertDatabaseCount('answer_details', 4);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(route('staff.forms.destroy', ['form' => $this->form]));

        $response->assertRedirect(route('staff.forms.index'));

        $this->assertDatabaseMissing('forms', [
            'name' => '削除対象のフォーム'
        ]);
        $this->assertDatabaseCount('questions', 0);
        $this->assertDatabaseCount('answers', 0);
        $this->assertDatabaseCount('answer_details', 0);
    }

    /**
     * @test
     */
    public function 権限がない場合はフォームを削除できない()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(route('staff.forms.destroy', ['form' => $this->form]));

        $response->assertForbidden();
    }
}
