<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers\Uploads;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class TableShowActionTest extends TestCase
{
    use RefreshDatabase;

    private Form $form;
    private Answer $answer;
    private Question $question;
    private string $rowId;
    private string $columnId;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->form = factory(Form::class)->create();
        $this->rowId = (string) Str::uuid();
        $this->columnId = (string) Str::uuid();
        $column = [
            'id' => $this->columnId,
            'name' => '添付',
            'type' => 'upload',
            'is_required' => false,
            'options' => null,
            'number_min' => null,
            'number_max' => 10,
            'allowed_types' => 'txt',
        ];
        $this->question = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'type' => 'table',
            'table' => [],
        ]);
        $circle = factory(Circle::class)->create();
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $circle->id,
        ]);
        factory(AnswerDetail::class)->create([
            'answer_id' => $this->answer->id,
            'question_id' => $this->question->id,
            'answer' => json_encode([
                'rows' => [$this->rowId => [$this->columnId => 'answer_details/table.txt']],
                'columns' => [$column],
            ]),
        ]);
        Storage::put('answer_details/table.txt', 'table upload');
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);
        $this->actingAs($user);
    }

    public function test_downloads_a_table_cell_file_from_its_exact_coordinates(): void
    {
        $response = $this->get($this->url())->assertOk();

        $this->assertSame(
            realpath(Storage::path('answer_details/table.txt')),
            $response->baseResponse->getFile()->getRealPath()
        );
        $this->get($this->url(['row' => (string) Str::uuid()]))->assertNotFound();
        $this->get($this->url(['column' => (string) Str::uuid()]))->assertNotFound();
    }

    public function test_cannot_download_another_circles_table_upload(): void
    {
        $this->actingAs(factory(User::class)->create());

        $this->get($this->url())->assertNotFound();
    }

    private function url(array $overrides = []): string
    {
        return route('forms.answers.uploads.table.show', array_merge([
            'form' => $this->form,
            'answer' => $this->answer,
            'question' => $this->question,
            'row' => $this->rowId,
            'column' => $this->columnId,
        ], $overrides));
    }
}
