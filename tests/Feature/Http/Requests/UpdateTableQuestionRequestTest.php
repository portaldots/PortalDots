<?php

namespace Tests\Feature\Http\Requests;

use App\Http\Requests\Staff\Forms\Editor\UpdateQuestionRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class UpdateTableQuestionRequestTest extends TestCase
{
    public function test_accepts_a_well_formed_table_definition(): void
    {
        $request = $this->makeRequest([
            $this->column('text'),
            $this->column('upload', ['allowed_types' => 'pdf|png']),
        ]);

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
    }

    public function test_rejects_duplicate_column_ids_nested_tables_and_negative_row_limits(): void
    {
        $id = (string) Str::uuid();
        $request = $this->makeRequest([
            $this->column('text', ['id' => $id]),
            $this->column('table', ['id' => $id]),
        ], -1, 2);

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('question.table', $validator->errors()->toArray());
    }

    public function test_accepts_an_empty_table_payload_for_a_non_table_question(): void
    {
        $request = UpdateQuestionRequest::create('/', 'PATCH', [
            'question' => [
                'type' => 'text',
                'priority' => 1,
                'table' => [],
            ],
        ]);

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails(), json_encode($validator->errors()->toArray()));
    }

    private function makeRequest(array $columns, ?int $minimum = 0, ?int $maximum = 10): UpdateQuestionRequest
    {
        return UpdateQuestionRequest::create('/', 'PATCH', [
            'question' => [
                'type' => 'table',
                'priority' => 1,
                'number_min' => $minimum,
                'number_max' => $maximum,
                'table' => $columns,
            ],
        ]);
    }

    private function column(string $type, array $overrides = []): array
    {
        return array_merge([
            'id' => (string) Str::uuid(),
            'name' => '列',
            'type' => $type,
            'is_required' => false,
            'options' => in_array($type, ['radio', 'checkbox', 'select'], true) ? "A\nB" : null,
            'number_min' => null,
            'number_max' => null,
            'allowed_types' => $type === 'upload' ? 'pdf' : null,
        ], $overrides);
    }
}
