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
            'open_at' => ['required', 'date'],
            'close_at' => ['required', 'date'],
            'is_public' => ['boolean'],
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
            'open_at' => '受付開始日時',
            'close_at' => '受付終了日時',
            'is_public' => '参加登録画面の公開設定',
        ];
    }
}
