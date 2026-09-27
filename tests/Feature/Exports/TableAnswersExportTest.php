<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Exports\AnswersExport;
use App\GridMakers\Helpers\AnswerDetailsHelper;
use App\Support\TableAnswerPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableAnswersExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_output_preserves_rows_and_deleted_columns_without_exposing_json(): void
    {
        $form = factory(Form::class)->create();
        $columns = [
            ['id' => 'text', 'name' => '名称', 'type' => 'text'],
            ['id' => 'file', 'name' => '添付', 'type' => 'upload'],
            ['id' => 'deleted', 'name' => '旧項目', 'type' => 'checkbox'],
        ];
        $question = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'table',
            'table' => array_slice($columns, 0, 2),
        ]);
        $answer = factory(Answer::class)->create(['form_id' => $form->id]);
        $stored = json_encode([
            'columns' => $columns,
            'rows' => [
                'first' => ['text' => '<script>alert(1)</script>', 'file' => 'answer_details/proof.pdf'],
                'second' => ['text' => '0', 'deleted' => ['A', 'B']],
            ],
        ]);
        factory(AnswerDetail::class)->create([
            'answer_id' => $answer->id, 'question_id' => $question->id, 'answer' => $stored,
        ]);
        $expected = "1件目・名称: <script>alert(1)</script>\n1件目・添付: proof.pdf\n"
            . "2件目・名称: 0\n2件目・旧項目（削除済みの項目）: A, B";
        $this->assertSame($expected, TableAnswerPresenter::text($question, $stored));
        $this->assertSame([$expected], (new AnswersExport($form))->getDetails($answer));

        $record = AnswerDetailsHelper::makeQueryWithAnswerDetails(
            Answer::query()->select('answers.id', 'q_' . $question->id),
            $form->questions,
            'q_',
            "\n"
        )->where('answers.id', $answer->id)->firstOrFail();
        $mapped = AnswerDetailsHelper::mapForAnswerDetails($record, $form->questions, $form, 'q_', "\n");
        $cells = $mapped['q_' . $question->id]['table_cells'];
        $this->assertCount(4, $cells);
        $this->assertSame('0', $cells[2]['text']);
        $this->assertStringContainsString('/first/file', $cells[1]['file_url']);
        $this->assertArrayNotHasKey('file_url', $cells[0]);

        $html = view('emails.includes.question_email', [
            'question' => $question, 'form' => $form, 'answer' => $answer,
            'answer_details' => [$question->id => ['first' => ['text' => 'value']]],
        ])->render();
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('/first/file', $html);
        $this->assertStringContainsString('削除済みの項目', $html);

        request()->setLaravelSession(session()->driver());
        session()->flashInput(['answers' => [$question->id => ['second' => ['text' => '0', '__present' => '1']]]]);
        $formHtml = view('includes.question', [
            'question' => $question, 'form' => $form, 'answer' => $answer,
            'answer_details' => [], 'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();
        $this->assertStringContainsString('&quot;deleted&quot;:[&quot;A&quot;,&quot;B&quot;]', $formHtml);
        $this->assertStringNotContainsString('&quot;first&quot;:', $formHtml);
    }

    public function test_unanswered_table_exports_an_empty_cell(): void
    {
        $form = factory(Form::class)->create();
        factory(Question::class)->create(['form_id' => $form->id, 'type' => 'table', 'table' => []]);
        $answer = factory(Answer::class)->create(['form_id' => $form->id]);
        $this->assertSame([''], (new AnswersExport($form))->getDetails($answer));
    }
}
