<?php

namespace App\Http\Requests\Staff\Pages;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
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
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
            'viewable_tags' => ['nullable', 'array'],
            'send_emails' => ['boolean'],
            'notes' => ['nullable'],
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
            'title' => 'タイトル',
            'body' => '本文',
            'viewable_tags' => 'お知らせを閲覧可能なユーザー',
            'send_emails' => 'メール配信',
            'notes' => 'スタッフ用メモ',
        ];
    }
}
