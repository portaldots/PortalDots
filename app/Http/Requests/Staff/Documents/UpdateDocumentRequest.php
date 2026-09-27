<?php

namespace App\Http\Requests\Staff\Documents;

use App\Contracts\AudiencePolicy;
use Illuminate\Foundation\Http\FormRequest;
use App;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
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
            'file' => ['nullable', 'file'],
            'is_public' => ['required', 'boolean'],
            'is_important' => ['required', 'boolean'],
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
            'audience' => '公開範囲',
            'viewable_tags' => '配布資料を閲覧可能なタグ',
            'viewable_circles' => '配布資料を閲覧可能な企画',
            'notes' => 'スタッフ用メモ',
        ];
    }

    public function messages()
    {
        return [];
    }
}
