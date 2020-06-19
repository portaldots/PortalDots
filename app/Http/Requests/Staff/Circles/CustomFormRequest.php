<?php

namespace App\Http\Requests\Staff\Circles;

use Illuminate\Foundation\Http\FormRequest;

class CustomFormRequest extends FormRequest
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
            'users_number_to_submit_circle' => ['required', 'integer', 'min:1'],
            'open_at' => ['required', 'date'],
            'close_at' => ['required', 'date', 'after:open_at'],
            'is_public' => ['boolean'],
            'description' => ['nullable', 'string'],
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
            'users_number_to_submit_circle' => '企画参加登録を提出するために必要な企画担当者の最低人数',
            'open_at' => '受付開始日時',
            'close_at' => '受付終了日時',
            'is_public' => '参加登録画面の公開設定',
            'description' => '参加登録前に表示する内容',
        ];
    }
}
