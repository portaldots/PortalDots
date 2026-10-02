<?php

namespace Tests\Feature\Http\Requests;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Eloquents\Question;
use App\Http\Requests\Circles\CircleRequest;
use App\Services\Forms\ValidationRulesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class CircleTableAnswerRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_participation_form_normalizes_rows_and_validates_keep_against_the_saved_cell(): void
    {
        $form = factory(Form::class)->create();
        $participationType = ParticipationType::factory()->create(['form_id' => $form->id]);
        $circle = factory(Circle::class)->create(['participation_type_id' => $participationType->id]);
        $textColumn = (string) Str::uuid();
        $uploadColumn = (string) Str::uuid();
        $row = (string) Str::uuid();
        $blankRow = (string) Str::uuid();
        $columns = [
            $this->column($textColumn, 'text'),
            $this->column($uploadColumn, 'upload'),
        ];
        $question = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'table',
            'is_required' => false,
            'number_min' => null,
            'number_max' => null,
            'table' => $columns,
        ]);
        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $circle->id,
        ]);
        factory(AnswerDetail::class)->create([
            'answer_id' => $answer->id,
            'question_id' => $question->id,
            'answer' => json_encode([
                'rows' => [$row => [$textColumn => 'old', $uploadColumn => 'answer_details/file.pdf']],
                'columns' => $columns,
            ]),
        ]);
        $request = CircleRequest::create('/', 'PATCH', [
            'participation_type' => $participationType->id,
            'answers' => [
                $question->id => [
                    $row => [$textColumn => "new\r\ntext", $uploadColumn => '__KEEP__'],
                    $blankRow => [$textColumn => ''],
                ],
            ],
        ]);
        $route = new class ($circle) {
            public function __construct(private Circle $circle)
            {
            }

            public function parameter(string $key, mixed $default = null): mixed
            {
                return $key === 'circle' ? $this->circle : $default;
            }
        };
        $request->setRouteResolver(fn () => $route);

        $data = $request->validationData();
        $rules = app(ValidationRulesService::class)->getRulesFromForm($form, $request);
        $validator = Validator::make($data, $rules);

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
        $this->assertSame([$row], array_keys($data['answers'][$question->id]));
        $this->assertSame("new\ntext", $data['answers'][$question->id][$row][$textColumn]);
    }

    private function column(string $id, string $type): array
    {
        return [
            'id' => $id,
            'name' => $type,
            'type' => $type,
            'is_required' => false,
            'options' => null,
            'number_min' => null,
            'number_max' => $type === 'upload' ? 10 : null,
            'allowed_types' => $type === 'upload' ? 'pdf' : null,
        ];
    }
}
