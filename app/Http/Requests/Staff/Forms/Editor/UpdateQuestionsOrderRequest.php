<?php

namespace App\Http\Requests\Staff\Forms\Editor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionsOrderRequest extends FormRequest
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
            'questions.*.id' => ['required', 'integer'],
            'questions.*.priority' => ['required', 'integer'],
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
            'questions.*.id' => '設問ID',
            'questions.*.priority' => '設問表示順優先度',
        ];
    }
}
