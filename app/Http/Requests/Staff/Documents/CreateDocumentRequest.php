<?php

namespace App\Http\Requests\Staff\Documents;

use Illuminate\Foundation\Http\FormRequest;

class CreateDocumentRequest extends FormRequest
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
            'file' => ['required', 'file'],
            'is_public' => ['required', 'boolean'],
            'is_important' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
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
            'name' => '配布資料名',
            'description' => '説明',
            'file' => 'ファイル',
            'is_public' => '公開設定',
            'is_important' => 'この配布資料は重要かどうか',
            'notes' => 'スタッフ用メモ',
        ];
    }

    public function messages()
    {
        return [];
    }
}
