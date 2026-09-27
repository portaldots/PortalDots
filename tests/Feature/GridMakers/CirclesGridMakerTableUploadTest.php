<?php

namespace Tests\Feature\GridMakers;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Eloquents\Question;
use App\GridMakers\CirclesGridMaker;
use App\GridMakers\Filter\FilterQueries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CirclesGridMakerTableUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_table_upload_url_uses_the_participation_answer_id(): void
    {
        $form = factory(Form::class)->create();
        $participationType = ParticipationType::factory()->create(['form_id' => $form->id]);
        factory(Circle::class)->create(['participation_type_id' => $participationType->id]);
        $circle = factory(Circle::class)->create(['participation_type_id' => $participationType->id]);
        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $circle->id,
        ]);
        $rowId = (string) Str::uuid();
        $columnId = (string) Str::uuid();
        $column = ['id' => $columnId, 'name' => '添付', 'type' => 'upload'];
        $question = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'table',
            'table' => [$column],
        ]);
        factory(AnswerDetail::class)->create([
            'answer_id' => $answer->id,
            'question_id' => $question->id,
            'answer' => json_encode([
                'columns' => [$column],
                'rows' => [$rowId => [$columnId => 'answer_details/proof.pdf']],
            ]),
        ]);

        $this->assertNotSame($circle->id, $answer->id);

        $records = app(CirclesGridMaker::class)
            ->withParticipationType($participationType)
            ->getArray('id', 'asc', new FilterQueries([]), 'and', 0, 10);
        $record = collect($records)->firstWhere('id', $circle->id);
        $cells = $record[CirclesGridMaker::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX . $question->id]['table_cells'];

        $this->assertStringContainsString(
            "/answers/{$answer->id}/uploads/{$question->id}/{$rowId}/{$columnId}",
            $cells[0]['file_url']
        );
    }
}
