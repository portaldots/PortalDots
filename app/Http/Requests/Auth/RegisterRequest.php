<?php

namespace App\Http\Requests\Auth;

use App\Eloquents\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $student_id
 * @property-read string $name
 * @property-read string $name_yomi
 * @property-read string $email
 * @property-read string $tel
 * @property-read string $password
 */
class RegisterRequest extends FormRequest
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
            'student_id' => ['string', 'unique:users'],
            'name' => ['required', 'string', 'max:255', 'regex:/^([^\s　]+)([\s　]+)([^\s　]+)$/u'],
            // 姓と名の間であれば，何個でもスペースを入れてもよしとする
            'name_yomi' => ['required', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)([\s　]+)([ぁ-んァ-ヶー]+)$/u'],
            // 姓と名の間であれば，何個でもスペースを入れてもよしとする
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'tel' => ['required', 'string', 'max:255'],
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
            'student_id' => '学籍番号',
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
            'student_id.unique' => '入力された学籍番号はすでに登録されています',
            'email.unique' => '入力されたメールアドレスはすでに登録されています',
            'name.regex' => '姓と名の間にはスペースを入れてください',
            'name_yomi.regex' => '姓と名の間にはスペースを入れてください。また、ひらがなで記入してください',
            // ひらがなもカタカナも入力可能だが，説明が面倒なので，エラー上ではひらがなでの記入を促す
        ];
    }
}
