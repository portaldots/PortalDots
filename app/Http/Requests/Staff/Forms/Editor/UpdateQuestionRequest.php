<?php

namespace App\Http\Requests\Staff\Forms\Editor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Eloquents\Question;
use App\Eloquents\AnswerDetail;
use App\Services\Forms\AnswerDetailsService;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $tableRules = ['nullable', 'array'];
        if ($this->input('question.type') === 'table') {
            $tableRules = [
                'required',
                'array',
                'min:1',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (is_array($value)) {
                        $this->validateTableDefinition($value, $fail);
                    }
                },
            ];
        }

        return [
            'question.allowed_types' => ['nullable', 'string'],
            'question.description' => ['nullable', 'string'],
            'question.is_required' => ['nullable', 'boolean'],
            'question.name' => ['nullable', 'string'],
            'question.number_max' => ['nullable', 'integer'],
            'question.number_min' => ['nullable', 'integer'],
            'question.options' => ['nullable', 'string'],
            'question.priority' => ['required', 'integer'],
            'question.table' => $tableRules,
            'question.type' => ['required', Rule::in(Question::QUESTION_TYPES)],
        ];
    }

    /**
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'question.allowed_types' => '設問の許可される拡張子',
            'question.description' => '設問の説明',
            'question.is_required' => '設問の回答必須',
            'question.name' => '設問名',
            'question.number_max' => '設問の最大数',
            'question.number_min' => '設問の最低数',
            'question.options' => '設問の選択肢',
            'question.priority' => '設問の設問表示順優先度',
            'question.table' => '繰り返し入力設問の項目定義',
            'question.type' => '設問タイプ',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $questionId = $this->input('question.id');
            $question = is_numeric($questionId) ? Question::find((int) $questionId) : null;
            if (!$question instanceof Question) {
                return;
            }

            $newType = $this->input('question.type');
            $storedAnswers = AnswerDetail::where('question_id', $question->id)->pluck('answer');
            if ($storedAnswers->isEmpty()) {
                return;
            }
            if (($question->type === 'table') !== ($newType === 'table')) {
                $validator->errors()->add(
                    'question.type',
                    '回答済みの繰り返し入力設問は別の設問タイプへ変更できません。'
                );
                return;
            }
            if ($question->type !== 'table' || !is_array($this->input('question.table'))) {
                return;
            }

            $oldTypes = collect($question->table ?? [])->pluck('type', 'id');
            $newTypes = collect($this->input('question.table'))->pluck('type', 'id');
            foreach ($oldTypes as $columnId => $oldType) {
                if (!$newTypes->has($columnId) || $newTypes->get($columnId) === $oldType) {
                    continue;
                }
                $hasStoredValue = $storedAnswers->contains(function ($stored) use ($columnId) {
                    $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($stored);
                    foreach ($envelope['rows'] as $row) {
                        if (is_array($row) && array_key_exists($columnId, $row)) {
                            return true;
                        }
                    }
                    return false;
                });
                if ($hasStoredValue) {
                    $validator->errors()->add(
                        'question.table',
                        "回答済みの項目 {$columnId} は種類を変更せず、新しい項目として追加してください。"
                    );
                }
            }

            foreach ($newTypes as $columnId => $newType) {
                $conflictsWithSnapshot = $storedAnswers->contains(function ($stored) use ($columnId, $newType) {
                    $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($stored);
                    $snapshotType = collect($envelope['columns'])->firstWhere('id', $columnId)['type'] ?? null;
                    if ($snapshotType === null || $snapshotType === $newType) {
                        return false;
                    }
                    foreach ($envelope['rows'] as $row) {
                        if (is_array($row) && array_key_exists($columnId, $row)) {
                            return true;
                        }
                    }
                    return false;
                });
                if ($conflictsWithSnapshot) {
                    $validator->errors()->add(
                        'question.table',
                        "保存済みの項目 {$columnId} と異なる種類で同じIDを再利用できません。"
                    );
                }
            }
        });
    }

    private function validateTableDefinition(array $columns, \Closure $fail): void
    {
        $allowedKeys = [
            'id', 'name', 'type', 'is_required', 'options',
            'number_min', 'number_max', 'allowed_types',
        ];
        $allowedTypes = ['text', 'textarea', 'number', 'radio', 'checkbox', 'select', 'upload'];
        $ids = [];

        foreach ($columns as $index => $column) {
            $label = '項目' . ((int) $index + 1);
            if (!is_array($column)) {
                $fail("{$label}は項目定義の配列で指定してください。");
                continue;
            }

            $unknownKeys = array_diff(array_keys($column), $allowedKeys);
            if ($unknownKeys !== []) {
                $fail("{$label}に未対応の項目が含まれています。");
            }

            $id = $column['id'] ?? null;
            if (!is_string($id) || !\Illuminate\Support\Str::isUuid($id)) {
                $fail("{$label}のIDにはUUIDを指定してください。");
            } elseif (isset($ids[$id])) {
                $fail("{$label}のIDが重複しています。");
            } else {
                $ids[$id] = true;
            }

            if (!isset($column['name']) || !is_string($column['name']) || trim($column['name']) === '') {
                $fail("{$label}の名前を指定してください。");
            }

            $type = $column['type'] ?? null;
            if (!is_string($type) || !in_array($type, $allowedTypes, true)) {
                $fail("{$label}の種類が無効です。");
            }
            if (!array_key_exists('is_required', $column) || !is_bool($column['is_required'])) {
                $fail("{$label}の必須設定には真偽値を指定してください。");
            }

            foreach (['number_min', 'number_max'] as $key) {
                if (isset($column[$key]) && filter_var($column[$key], FILTER_VALIDATE_INT) === false) {
                    $fail("{$label}の最小値・最大値には整数を指定してください。");
                }
            }
            if (
                isset($column['number_min'], $column['number_max']) &&
                (int) $column['number_max'] < (int) $column['number_min']
            ) {
                $fail("{$label}の最大値は最小値以上にしてください。");
            }

            foreach (['options', 'allowed_types'] as $key) {
                if (isset($column[$key]) && !is_string($column[$key])) {
                    $fail("{$label}の{$key}には文字列を指定してください。");
                }
            }

            if (in_array($type, ['radio', 'checkbox', 'select'], true) && empty($column['options'])) {
                $fail("{$label}の選択肢を指定してください。");
            }
            if ($type === 'upload' && empty($column['allowed_types'])) {
                $fail("{$label}の許可される拡張子を指定してください。");
            }
        }

        $minimum = $this->input('question.number_min');
        $maximum = $this->input('question.number_max');
        if ($minimum !== null && ((int) $minimum < 0 || filter_var($minimum, FILTER_VALIDATE_INT) === false)) {
            $fail('繰り返し入力設問の最小件数には0以上の整数を指定してください。');
        }
        if ($maximum !== null && ((int) $maximum < 0 || filter_var($maximum, FILTER_VALIDATE_INT) === false)) {
            $fail('繰り返し入力設問の最大件数には0以上の整数を指定してください。');
        }
        if ($minimum !== null && $maximum !== null && (int) $maximum < (int) $minimum) {
            $fail('繰り返し入力設問の最大件数は最小件数以上にしてください。');
        }
    }
}
