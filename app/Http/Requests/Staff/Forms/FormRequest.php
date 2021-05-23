<?php

namespace App\Http\Requests\Staff\Forms;

use Illuminate\Foundation\Http\FormRequest as BaseRequest;

class FormRequest extends BaseRequest
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
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'open_at' => ['required', 'date'],
            'close_at' => ['required', 'date', 'after:open_at'],
            'max_answers' => ['required', 'integer', 'min:1'],
            'is_public' => ['boolean'],
            'answerable_tags' => ['nullable', 'array'],
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
            'name' => 'フォーム名',
            'description' => 'フォームの説明',
            'open_at' => '受付開始日時',
            'close_at' => '受付終了日時',
            'max_answers' => '企画毎に回答可能とする回答数',
            'is_public' => '公開設定',
            'answerable_tags' => 'フォームへ回答可能なユーザー',
        ];
    }
}
