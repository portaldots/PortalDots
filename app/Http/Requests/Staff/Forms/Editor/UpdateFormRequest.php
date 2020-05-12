<?php

namespace App\Http\Requests\Staff\Forms\Editor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
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
            'form.name' => ['required', 'string'],
            'form.description' => ['nullable', 'string'],
            'form.is_public' => ['required', 'boolean'],
            'form.max_answers' => ['required', 'integer'],
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
            'form.name' => 'タイトル',
            'form.description' => '説明',
            'form.is_public' => '公開',
            'form.max_answers' => '回答可能数',
        ];
    }
}
