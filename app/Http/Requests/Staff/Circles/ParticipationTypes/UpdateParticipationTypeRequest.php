<?php

namespace App\Http\Requests\Staff\Circles\ParticipationTypes;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParticipationTypeRequest extends FormRequest
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
            'tags' => ['nullable', 'array'],
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
            'name' => '参加種別名',
            'description' => '説明',
            'tags' => '作成した企画に自動で追加するタグ',
        ];
    }
}
