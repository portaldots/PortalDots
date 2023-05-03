<?php

namespace App\Http\Requests\Staff\Circles\ParticipationTypes;

use Illuminate\Foundation\Http\FormRequest;

class ParticipationFormRequest extends FormRequest
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
            'users_count_min' => ['required', 'integer', 'min:1'],
            'users_count_max' => ['required', 'integer', 'min:1', 'gte:users_count_min'],
            'open_at' => ['required', 'date'],
            'close_at' => ['required', 'date', 'after:open_at'],
            'is_public' => ['boolean'],
            'form_description' => ['nullable', 'string'],
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
            'users_count_min' => '最低人数',
            'users_count_max' => '最大人数',
            'open_at' => '受付開始日時',
            'close_at' => '受付終了日時',
            'is_public' => '参加登録画面の公開設定',
            'description' => '参加登録前に表示する内容',
        ];
    }
}
