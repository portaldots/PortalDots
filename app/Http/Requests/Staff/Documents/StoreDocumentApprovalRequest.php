<?php

namespace App\Http\Requests\Staff\Documents;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentApprovalRequest extends FormRequest
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
        /** @var Document $document */
        $document = $this->route('document');

        return [
            'circles' => ['required', 'array'],
            'circles.*' => [
                'integer',
                'exists:circles,id',
                function ($attribute, $value, $fail) use ($document) {
                    $circle = Circle::find($value);
                    if (empty($circle)) {
                        return;
                    }

                    // signed_in の公開範囲を判定するためだけのダミーのUserインスタンス
                    // （企画は常にログイン済みユーザーとして扱われるため、身元は問わない）
                    $isVisible = Document::whereKey($document->id)->visibleTo(new User(), $circle)->exists();
                    if (!$isVisible) {
                        $fail("「{$circle->name}」はこの配布資料を閲覧できないため、確認を依頼できません。");
                    }
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
            'circles' => '確認を依頼する企画',
        ];
    }
}
