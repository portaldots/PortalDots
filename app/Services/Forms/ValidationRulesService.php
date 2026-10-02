<?php

declare(strict_types=1);

namespace App\Services\Forms;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Eloquents\Form;
use App\Eloquents\Answer;
use App\Eloquents\Circle;

class ValidationRulesService
{
    private AnswerDetailsService $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        $this->answerDetailsService = $answerDetailsService;
    }

    /**
     * バリデーションルール配列を取得する
     *
     * 回答の下書き保存用の簡易バリデーションとしたい場合、$isStrict 引数の値を false にする
     *
     * @param Form $form
     * @param Request $request
     * @param bool $isStrict 厳密なバリデーションルールにするか
     * @return void
     */
    public function getRulesFromForm(Form $form, Request $request, bool $isStrict = true)
    {
        $questions = $form->questions;

        $rules = [
            'answers' => ['nullable', 'array'],
        ];

        foreach ($questions as $question) {
            if ($question->type === 'table') {
                $rules += $this->getTableRules($question, $request, $isStrict);
                continue;
            }

            $rule = [];

            // 回答必須かどうか
            if ($question->is_required && $isStrict) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            // 型チェック
            if (in_array($question->type, ['text', 'textarea'], true)) {
                $rule[] = 'string';
            } elseif ($question->type === 'checkbox') {
                $rule[] = 'array';
            } elseif ($question->type === 'number') {
                $rule[] = 'integer';
            } elseif ($question->type === 'upload') {
                $rule[] = 'file';
            }

            // 文字数・整数値・ファイルサイズ範囲制限
            if (
                in_array($question->type, ['text', 'textarea', 'number', 'checkbox'], true) &&
                isset($question->number_min) && $isStrict
            ) {
                // upload に対しては、最小ファイルサイズを検証しない
                $rule[] = 'min:' . $question->number_min;
            }

            if (
                in_array($question->type, ['text', 'textarea', 'number', 'checkbox', 'upload'], true) &&
                isset($question->number_max) && $isStrict
            ) {
                $rule[] = 'max:' . $question->number_max;
            }

            // ファイルの種類チェック
            if ($question->type === 'upload') {
                $rule[] = 'mimes:' . \implode(',', $question->allowed_types_array);
            }

            // ファイルアップロード設問において、回答が "__KEEP__" だった場合、
            // 以前アップロードしたファイルを回答として利用したいということなので、
            // 回答のバリデーションは行わない
            if (
                $question->type === 'upload' && isset($request->answers[$question->id]) &&
                $request->answers[$question->id] === '__KEEP__'
            ) {
                $rule = ['required'];
            }

            $rules['answers.' . $question->id] = $rule;

            // 用意された選択肢の中から選択されているか
            if (in_array($question->type, ['radio', 'select', 'checkbox'], true)) {
                $rules['answers.' . $question->id . '.*'] = Rule::in($question->options_array);
            }
        }

        return $rules;
    }

    private function getTableRules($question, Request $request, bool $isStrict): array
    {
        $attribute = 'answers.' . $question->id;
        $allAnswers = $request->validationData()['answers'] ?? [];
        $rows = is_array($allAnswers) ? ($allAnswers[$question->id] ?? []) : null;
        $rootRule = $question->is_required && $isStrict ? ['required', 'array'] : ['nullable', 'array'];
        if (
            $isStrict && isset($question->number_min) &&
            ($question->is_required || (is_array($rows) && $rows !== []))
        ) {
            $rootRule[] = 'min:' . $question->number_min;
        }
        if ($isStrict && isset($question->number_max)) {
            $rootRule[] = 'max:' . $question->number_max;
        }

        $columns = is_array($question->table) ? $question->table : [];
        $columnsById = [];
        foreach ($columns as $column) {
            if (is_array($column) && isset($column['id']) && is_string($column['id'])) {
                $columnsById[$column['id']] = $column;
            }
        }

        $rootRule[] = function (string $unused, mixed $rows, \Closure $fail) use ($columnsById) {
            if (!is_array($rows)) {
                return;
            }
            foreach ($rows as $rowId => $row) {
                if (!is_string($rowId) || !Str::isUuid($rowId)) {
                    $fail('繰り返し入力の回答IDにはUUIDを指定してください。');
                    continue;
                }
                if (!is_array($row)) {
                    $fail("回答 {$rowId} は項目ごとの値を含む配列で指定してください。");
                    continue;
                }
                foreach (array_keys($row) as $columnId) {
                    if (!isset($columnsById[$columnId])) {
                        $fail("回答 {$rowId} に定義されていない項目 {$columnId} が含まれています。");
                    }
                }
            }
        };
        $rules = [$attribute => $rootRule];

        if (!is_array($rows)) {
            return $rules;
        }

        $answer = $request->route('answer');
        if (!$answer instanceof Answer) {
            $circle = $request->route('circle');
            $answer = $circle instanceof Circle ? $circle->getParticipationFormAnswer() : null;
        }
        $oldEnvelope = $answer instanceof Answer
            ? $this->answerDetailsService->getTableAnswerEnvelopeByAnswer($answer, (int) $question->id)
            : ['rows' => [], 'columns' => []];

        foreach ($rows as $rowId => $row) {
            if (!is_string($rowId) || !Str::isUuid($rowId) || !is_array($row)) {
                continue;
            }
            $rowAttribute = $attribute . '.' . $rowId;
            $rules[$rowAttribute] = ['array:' . implode(',', array_keys($columnsById))];

            foreach ($columnsById as $columnId => $column) {
                $cellAttribute = $rowAttribute . '.' . $columnId;
                $value = $row[$columnId] ?? null;
                $cellRules = !empty($column['is_required']) && $isStrict ? ['required'] : ['nullable'];
                $type = $column['type'] ?? null;

                if (in_array($type, ['text', 'textarea'], true)) {
                    $cellRules[] = 'string';
                } elseif ($type === 'number') {
                    $cellRules[] = 'integer';
                } elseif (in_array($type, ['radio', 'select'], true)) {
                    $cellRules[] = 'string';
                } elseif ($type === 'checkbox') {
                    $cellRules[] = 'array';
                } elseif ($type === 'upload') {
                    if ($value === '__KEEP__') {
                        $oldValue = $oldEnvelope['rows'][$rowId][$columnId] ?? null;
                        $cellRules[] = function (string $unused, mixed $keep, \Closure $fail) use ($oldValue) {
                            if ($keep !== '__KEEP__' || !is_string($oldValue) || $oldValue === '') {
                                $fail('保持するアップロード済みファイルが見つかりません。');
                            }
                        };
                    } else {
                        $cellRules[] = 'file';
                        if (isset($column['number_max'])) {
                            $cellRules[] = 'max:' . (int) $column['number_max'];
                        }
                        $allowedTypes = $this->splitPipeValues($column['allowed_types'] ?? null);
                        if ($allowedTypes !== []) {
                            $cellRules[] = 'mimes:' . implode(',', $allowedTypes);
                        }
                    }
                }

                if ($isStrict && in_array($type, ['text', 'textarea', 'number', 'checkbox'], true)) {
                    if (isset($column['number_min'])) {
                        $cellRules[] = 'min:' . (int) $column['number_min'];
                    }
                    if (isset($column['number_max'])) {
                        $cellRules[] = 'max:' . (int) $column['number_max'];
                    }
                }

                $rules[$cellAttribute] = $cellRules;
                if (in_array($type, ['radio', 'select'], true)) {
                    $rules[$cellAttribute][] = Rule::in($this->splitLineValues($column['options'] ?? null));
                } elseif ($type === 'checkbox') {
                    $rules[$cellAttribute . '.*'] = [
                        'string',
                        Rule::in($this->splitLineValues($column['options'] ?? null)),
                    ];
                }
            }
        }

        return $rules;
    }

    private function splitLineValues(mixed $value): array
    {
        if (!is_string($value)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', preg_split('/\\R/u', $value)), 'strlen'));
    }

    private function splitPipeValues(mixed $value): array
    {
        if (!is_string($value)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode('|', $value)), 'strlen'));
    }

    public function getAttributesFromForm(Form $form)
    {
        return $form->questions->flatMap(function ($question) {
            $attributes = ['answers.' . $question->id => $question->name];
            if ($question->type === 'table' && is_array($question->table)) {
                foreach ($question->table as $column) {
                    if (isset($column['id'], $column['name'])) {
                        $attributes['answers.' . $question->id . '.*.' . $column['id']] =
                            $question->name . ' / ' . $column['name'];
                    }
                }
            }
            return $attributes;
        });
    }
}
