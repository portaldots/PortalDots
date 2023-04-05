<?php

namespace App\Http\Requests\Staff\Pages;

use Illuminate\Foundation\Http\FormRequest;

class PatchPinRequest extends FormRequest
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
            'is_pinned' => ['nullable', 'boolean'],
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
            'is_pinned' => 'お知らせを固定表示',
        ];
    }
}
