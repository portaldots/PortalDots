<?php

namespace App\Http\Requests\Staff\Circles;

use Illuminate\Foundation\Http\FormRequest;
use App\Eloquents\Circle;

class CircleRequest extends FormRequest
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
            'name' => Circle::NAME_RULES,
            'name_yomi' => Circle::NAME_YOMI_RULES,
            'status' => Circle::STATUS_RULES,
            'tags'    => ['nullable', 'array'],
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
            'name' => '企画名',
            'name_yomi' => '企画名(よみ)',
            'status' => '参加登録受理',
        ];
    }

    public function messages()
    {
        return [
            'name_yomi.regex' => 'ひらがなで入力してください',
            'group_name_yomi.regex' => 'ひらがなで入力してください',
            // ひらがなもカタカナも入力可能だが，説明が面倒なので，エラー上ではひらがなでの入力を促す
        ];
    }
}
