<?php

namespace App\Http\Requests\Staff\Pages;

use App\Contracts\AudiencePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'is_pinned' => ['nullable', 'boolean'],
            'is_public' => ['boolean'],
            'documents' => ['nullable', 'array'],
            'audience' => [
                'required',
                'string',
                Rule::in(app(AudiencePolicy::class)->allowedAudiences()),
                function ($attribute, $value, $fail) {
                    if (
                        $value === AudiencePolicy::SELECTED &&
                        empty($this->input('viewable_tags')) &&
                        empty($this->input('viewable_circles'))
                    ) {
                        $fail('公開範囲を「選んだタグ・企画のみ」にする場合、タグまたは企画を1つ以上指定してください。');
                    }
                },
            ],
            'viewable_tags' => ['nullable', 'array'],
            'viewable_circles' => ['nullable', 'array'],
            'viewable_circles.*' => ['integer', 'exists:circles,id'],
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
            'is_pinned' => 'お知らせを固定表示',
            'is_public' => '公開設定',
            'documents' => '関連する配布資料',
            'audience' => '公開範囲',
            'viewable_tags' => 'お知らせを閲覧可能なタグ',
            'viewable_circles' => 'お知らせを閲覧可能な企画',
            'send_emails' => 'メール配信',
            'notes' => 'スタッフ用メモ',
        ];
    }
}
