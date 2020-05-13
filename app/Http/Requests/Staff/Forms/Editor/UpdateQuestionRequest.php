<?php

namespace App\Http\Requests\Staff\Forms\Editor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Eloquents\Question;

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
        return [
            'question.allowed_types' => ['nullable', 'string'],
            'question.description' => ['nullable', 'string'],
            'question.is_required' => ['nullable', 'boolean'],
            'question.name' => ['nullable', 'string'],
            'question.number_max' => ['nullable', 'integer'],
            'question.number_min' => ['nullable', 'integer'],
            'question.options' => ['nullable', 'string'],
            'question.priority' => ['required', 'integer'],
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
            'question.type' => '設問タイプ',
        ];
    }
}
