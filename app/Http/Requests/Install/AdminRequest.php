<?php

namespace App\Http\Requests\Install;

use Illuminate\Foundation\Http\FormRequest;
use App\Eloquents\User;

class AdminRequest extends FormRequest
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
        // このバリデーションが実行されるタイミングでは、まだマイグレーション
        // が行われていない。そのため、ここでは unique ルールなど、
        // データベースへの接続が必要なルールを追加しないこと。
        return [
            'student_id' => User::STUDENT_ID_RULES,
            'name' => User::NAME_RULES,
            'name_yomi' => User::NAME_YOMI_RULES,
            'email' => User::EMAIL_RULES,
            'tel' => User::TEL_RULES,
            'password' => array_merge(User::PASSWORD_RULES, ['confirmed']),
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
            'student_id' => config('portal.student_id_name'),
            'name' => '名前',
            'name_yomi' => '名前(よみ)',
            'email' => '連絡先メールアドレス',
            'tel' => '連絡先電話番号',
            'password' => 'パスワード',
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
            'name.regex' => '姓と名の間にはスペースを入れてください',
            'name_yomi.regex' => '姓と名の間にはスペースを入れてください。また、ひらがなで記入してください',
            // ひらがなもカタカナも入力可能だが，説明が面倒なので，エラー上ではひらがなでの記入を促す
        ];
    }
}
