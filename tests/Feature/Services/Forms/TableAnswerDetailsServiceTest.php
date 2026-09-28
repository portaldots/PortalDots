<?php

namespace Tests\Feature\Services\Forms;

use App\Contracts\FileStorageLayout;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Services\Forms\AnswerDetailsService;
use App\Services\Forms\AnswersService;
use App\Services\Utils\ActivityLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class TableAnswerDetailsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Auth::login(factory(User::class)->create());
    }

    public function test_persists_one_envelope_and_retains_deleted_columns_for_rows_that_remain(): void
    {
        $form = factory(Form::class)->create();
        $currentColumn = (string) Str::uuid();
        $deletedColumn = (string) Str::uuid();
        $rowKept = (string) Str::uuid();
        $rowRemoved = (string) Str::uuid();
        $question = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'table',
            'table' => [$this->column($currentColumn, '現在', 'text')],
        ]);
        $answer = factory(Answer::class)->create(['form_id' => $form->id]);
        factory(AnswerDetail::class)->create([
            'answer_id' => $answer->id,
            'question_id' => $question->id,
            'answer' => json_encode([
                'rows' => [
                    $rowKept => [$currentColumn => 'old', $deletedColumn => 'retained'],
                    $rowRemoved => [$deletedColumn => 'removed'],
                ],
                'columns' => [
                    $this->column($currentColumn, '現在', 'text'),
                    $this->column($deletedColumn, '削除済み', 'text'),
                ],
            ]),
        ]);

        app(AnswerDetailsService::class)->updateAnswerDetails($form, $answer, [
            $question->id => [$rowKept => [$currentColumn => 'new']],
        ]);

        $details = AnswerDetail::where('answer_id', $answer->id)->get();
        $this->assertCount(1, $details);
        $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($details->sole()->answer);
        $this->assertSame([$rowKept], array_keys($envelope['rows']));
        $this->assertSame(
            [$currentColumn => 'new', $deletedColumn => 'retained'],
            $envelope['rows'][$rowKept]
        );
        $this->assertSame([$currentColumn, $deletedColumn], array_column($envelope['columns'], 'id'));
        $this->assertSame(
            $envelope['rows'],
            app(AnswerDetailsService::class)->getAnswerDetailsByAnswer($answer)[$question->id]
        );
    }

    public function test_deletes_replaced_and_removed_table_files_but_keeps_exact_keep_cells(): void
    {
        $form = factory(Form::class)->create();
        $uploadColumn = (string) Str::uuid();
        $rowKept = (string) Str::uuid();
        $rowRemoved = (string) Str::uuid();
        $column = $this->column($uploadColumn, '添付', 'upload');
        $question = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'table',
            'table' => [$column],
        ]);
        $answer = factory(Answer::class)->create(['form_id' => $form->id]);
        Storage::put('answer_details/kept.txt', 'kept');
        Storage::put('answer_details/removed.txt', 'removed');
        factory(AnswerDetail::class)->create([
            'answer_id' => $answer->id,
            'question_id' => $question->id,
            'answer' => json_encode([
                'rows' => [
                    $rowKept => [$uploadColumn => 'answer_details/kept.txt'],
                    $rowRemoved => [$uploadColumn => 'answer_details/removed.txt'],
                ],
                'columns' => [$column],
            ]),
        ]);

        app(AnswerDetailsService::class)->updateAnswerDetails($form, $answer, [
            $question->id => [$rowKept => [$uploadColumn => '__KEEP__']],
        ]);

        Storage::assertExists('answer_details/kept.txt');
        Storage::assertMissing('answer_details/removed.txt');
        $envelope = app(AnswerDetailsService::class)->getTableAnswerEnvelopeByAnswer($answer, $question->id);
        $this->assertSame('answer_details/kept.txt', $envelope['rows'][$rowKept][$uploadColumn]);
    }

    public function test_removes_new_table_files_when_the_database_transaction_rolls_back(): void
    {
        $form = factory(Form::class)->create();
        $columnId = (string) Str::uuid();
        $rowId = (string) Str::uuid();
        $question = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'table',
            'table' => [$this->column($columnId, '添付', 'upload')],
        ]);
        $circle = factory(Circle::class)->create();
        $file = UploadedFile::fake()->create('new.pdf', 2, 'application/pdf');
        $request = new TableAnswerRequestFake([
            $question->id => [$rowId => [$columnId => $file]],
        ]);
        $activityLog = $this->mock(ActivityLogService::class);
        $activityLog->shouldReceive('logOnlyAttributesChanged')->once()->andThrow(new RuntimeException('rollback'));
        $answerDetails = new AnswerDetailsService($activityLog, App::make(FileStorageLayout::class));
        $answers = new AnswersService($answerDetails);

        try {
            $answers->createAnswer($form, $circle, $request);
            $this->fail('The transaction should fail.');
        } catch (RuntimeException $e) {
            $this->assertSame('rollback', $e->getMessage());
        }

        $this->assertDatabaseMissing('answers', ['form_id' => $form->id, 'circle_id' => $circle->id]);
        $this->assertSame([], Storage::allFiles('answer_details'));
    }

    public function test_keep_literal_is_preserved_in_a_text_cell(): void
    {
        $form = factory(Form::class)->create();
        $columnId = (string) Str::uuid();
        $rowId = (string) Str::uuid();
        $question = factory(Question::class)->create([
            'form_id' => $form->id, 'type' => 'table',
            'table' => [$this->column($columnId, '文字列', 'text')],
        ]);
        $answer = factory(Answer::class)->create(['form_id' => $form->id]);
        $service = app(AnswerDetailsService::class);
        $service->updateAnswerDetails($form, $answer, [$question->id => [$rowId => [$columnId => '__KEEP__']]]);
        $this->assertSame('__KEEP__', $service->getAnswerDetailsByAnswer($answer)[$question->id][$rowId][$columnId]);
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
            'number_max' => $type === 'upload' ? 10 : null,
            'allowed_types' => $type === 'upload' ? 'pdf|txt' : null,
        ];
    }
}
