<?php

namespace App\Http\Requests\Staff\Forms;

use Illuminate\Foundation\Http\FormRequest;

class AnswerReturnRequest extends FormRequest
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
            'review_note' => ['required', 'string'],
            'lock_version' => ['nullable', 'integer'],
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
            'review_note' => '差し戻し理由',
        ];
    }
}
