<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Mail\Forms\AnswerConfirmationMailable;
use App\Services\Forms\AnswerDetailsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class TableStoreActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Circle $circle;
    private Form $form;

    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();
        $this->circle->users()->attach($this->user->id, ['is_leader' => true]);
        $this->form = factory(Form::class)->create(['max_answers' => 5]);
    }

    public function test_strips_blank_rows_normalizes_nested_newlines_and_preserves_row_order(): void
    {
        [$question, $requiredColumn, $checkboxColumn] = $this->makeQuestion();
        $firstRow = (string) Str::uuid();
        $blankRow = (string) Str::uuid();

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [
                $question->id => [
                    $firstRow => [
                        $requiredColumn => "line 1\r\nline 2",
                        $checkboxColumn => ['A'],
                    ],
                    $blankRow => [$requiredColumn => '', $checkboxColumn => []],
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $detail = AnswerDetail::where('question_id', $question->id)->sole();
        $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($detail->answer);
        $this->assertSame([$firstRow], array_keys($envelope['rows']));
        $this->assertSame("line 1\nline 2", $envelope['rows'][$firstRow][$requiredColumn]);
        $this->assertSame(['A'], $envelope['rows'][$firstRow][$checkboxColumn]);
        Mail::assertSent(AnswerConfirmationMailable::class);
    }

    public function test_reports_required_cells_and_rejects_unknown_columns(): void
    {
        [$question, $requiredColumn, $checkboxColumn] = $this->makeQuestion();
        $row = (string) Str::uuid();

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [
                $question->id => [
                    $row => [
                        $checkboxColumn => ['A'],
                        (string) Str::uuid() => 'forged',
                    ],
                ],
            ],
        ]);

        $response->assertSessionHasErrors([
            "answers.{$question->id}.{$row}.{$requiredColumn}",
            "answers.{$question->id}",
        ]);
        $this->assertDatabaseCount('answers', 0);
    }

    public function test_optional_empty_table_does_not_apply_its_minimum_row_count(): void
    {
        [$question, $requiredColumn, $checkboxColumn] = $this->makeQuestion(false, 2);
        $blankRow = (string) Str::uuid();

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [$question->id => [$blankRow => [$requiredColumn => '', $checkboxColumn => null]]],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('answers', 1);
        $detail = AnswerDetail::where('question_id', $question->id)->sole();
        $this->assertSame([], AnswerDetailsService::decodeTableAnswerEnvelope($detail->answer)['rows']);
    }

    public function test_forged_presence_marker_does_not_satisfy_the_minimum_row_count(): void
    {
        [$question, $requiredColumn] = $this->makeQuestion(false, 2);
        $validRow = (string) Str::uuid();
        $forgedRow = (string) Str::uuid();

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [
                $question->id => [
                    $validRow => [$requiredColumn => 'value'],
                    $forgedRow => ['__present' => '1', $requiredColumn => ''],
                ],
            ],
        ]);

        $response->assertSessionHasErrors("answers.{$question->id}");
        $this->assertDatabaseCount('answers', 0);
    }

    public function test_presence_marker_preserves_a_saved_row_with_only_deleted_column_data(): void
    {
        $currentColumn = (string) Str::uuid();
        $deletedColumn = (string) Str::uuid();
        $row = (string) Str::uuid();
        $currentDefinition = $this->column($currentColumn, '現在', 'text');
        $deletedDefinition = $this->column($deletedColumn, '削除済み', 'text');
        $question = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'type' => 'table',
            'is_required' => false,
            'number_min' => null,
            'number_max' => null,
            'table' => [$currentDefinition],
        ]);
        $answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);
        factory(AnswerDetail::class)->create([
            'answer_id' => $answer->id,
            'question_id' => $question->id,
            'answer' => json_encode([
                'rows' => [$row => [$deletedColumn => 'saved']],
                'columns' => [$currentDefinition, $deletedDefinition],
            ]),
        ]);

        $response = $this->actingAs($this->user)->patch(route('forms.answers.update', [
            'form' => $this->form,
            'answer' => $answer,
        ]), [
            'answers' => [
                $question->id => [
                    $row => ['__present' => '1', $currentColumn => ''],
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $stored = AnswerDetail::where('answer_id', $answer->id)->where('question_id', $question->id)->sole();
        $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($stored->answer);
        $this->assertSame([$deletedColumn => 'saved'], $envelope['rows'][$row]);
    }

    public function test_rejects_a_scalar_answers_root_without_throwing(): void
    {
        [$question] = $this->makeQuestion();

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => 'forged',
        ]);

        $response->assertSessionHasErrors('answers');
        $this->assertDatabaseCount('answers', 0);

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [$question->id => 'forged'],
        ]);

        $response->assertSessionHasErrors("answers.{$question->id}");
        $this->assertDatabaseCount('answers', 0);
    }

    public function test_rejects_malformed_blank_rows_and_cell_shapes(): void
    {
        [$question, $textColumn, $checkboxColumn] = $this->makeQuestion();
        $nonArrayRow = (string) Str::uuid();
        $unknownColumnRow = (string) Str::uuid();
        $textArrayRow = (string) Str::uuid();
        $nestedCheckboxRow = (string) Str::uuid();

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [
                $question->id => [
                    'invalid-row-id' => [$textColumn => ''],
                    $nonArrayRow => '',
                    $unknownColumnRow => [(string) Str::uuid() => ''],
                    $textArrayRow => [$textColumn => []],
                    $nestedCheckboxRow => [$textColumn => 'valid', $checkboxColumn => [['A']]],
                ],
            ],
        ]);

        $response->assertSessionHasErrors([
            "answers.{$question->id}",
            "answers.{$question->id}.{$textArrayRow}.{$textColumn}",
            "answers.{$question->id}.{$nestedCheckboxRow}.{$checkboxColumn}.0",
        ]);
        $this->assertDatabaseCount('answers', 0);
    }

    private function makeQuestion(bool $isRequired = false, int $minimum = 1): array
    {
        $requiredColumn = (string) Str::uuid();
        $checkboxColumn = (string) Str::uuid();
        $question = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'type' => 'table',
            'is_required' => $isRequired,
            'number_min' => $minimum,
            'number_max' => 3,
            'table' => [
                [
                    'id' => $requiredColumn,
                    'name' => '本文',
                    'type' => 'textarea',
                    'is_required' => true,
                    'options' => null,
                    'number_min' => null,
                    'number_max' => null,
                    'allowed_types' => null,
                ],
                [
                    'id' => $checkboxColumn,
                    'name' => '選択',
                    'type' => 'checkbox',
                    'is_required' => false,
                    'options' => "A\nB",
                    'number_min' => null,
                    'number_max' => null,
                    'allowed_types' => null,
                ],
            ],
        ]);
        return [$question, $requiredColumn, $checkboxColumn];
    }

    private function column(string $id, string $name, string $type): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'is_required' => false,
            'options' => null,
            'number_min' => null,
            'number_max' => null,
            'allowed_types' => null,
        ];
    }
}
