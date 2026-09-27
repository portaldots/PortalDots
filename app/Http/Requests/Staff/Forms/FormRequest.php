<?php

namespace App\Http\Requests\Staff\Forms;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Form;
use Illuminate\Foundation\Http\FormRequest as BaseRequest;
use Illuminate\Validation\Rule;

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
            'confirmation_message' => ['nullable', 'string'],
            'open_at' => ['required', 'date'],
            'close_at' => ['required', 'date', 'after:open_at'],
            'max_answers' => ['required', 'integer', 'min:1'],
            'is_public' => ['boolean'],
            'answerable_tags' => ['nullable', 'array'],
            'requires_review' => ['boolean'],
            'audience' => [
                'required',
                'string',
                Rule::in(Form::allowedAudiences(app(AudiencePolicy::class))),
                function ($attribute, $value, $fail) {
                    if ($value !== AudiencePolicy::SELECTED) {
                        return;
                    }
                    if (!empty($this->input('answerable_tags'))) {
                        return;
                    }

                    /** @var Form|null $form */
                    $form = $this->route('form');
                    if (!empty($form) && $form->assignments()->exists()) {
                        return;
                    }

                    $fail('公開範囲を「選んだタグ・企画のみ」にする場合、タグを指定するか、送付先の企画を1つ以上追加してください。');
                },
            ],
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
            'confirmation_message' => '回答後に表示する内容',
            'open_at' => '受付開始日時',
            'close_at' => '受付終了日時',
            'max_answers' => '企画毎に回答可能とする回答数',
            'is_public' => '公開設定',
            'answerable_tags' => 'フォームへ回答可能なユーザー',
            'requires_review' => '提出後の確認',
            'audience' => '公開範囲',
        ];
    }
}
