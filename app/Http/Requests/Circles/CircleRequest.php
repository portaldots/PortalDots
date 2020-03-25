<?php

namespace App\Http\Requests\Circles;

use App\Eloquents\Circle;
use Illuminate\Foundation\Http\FormRequest;

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
            'group_name' => Circle::GROUP_NAME_RULES,
            'group_name_yomi' => Circle::GROUP_NAME_YOMI_RULES,
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
            'name' => '企画の名前',
            'name_yomi' => '企画の名前(よみ)',
            'group_name' => '企画団体の名前',
            'group_name_yomi' => '企画団体の名前(よみ)',
        ];
    }

    /**
     * バリデーションエラーメッセージ取得
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name_yomi.regex' => 'ひらがなで記入してください',
            'group_name_yomi.regex' => 'ひらがなで記入してください',
            // ひらがなもカタカナも入力可能だが，説明が面倒なので，エラー上ではひらがなでの記入を促す
        ];
    }
}
