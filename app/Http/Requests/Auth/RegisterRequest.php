<?php

namespace App\Http\Requests\Auth;

use App\Eloquents\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $student_id
 * @property-read string $name
 * @property-read string $name_yomi
 * @property-read string $email
 * @property-read string $univemail_local_part
 * @property-read string $univemail_domain_part
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
        $rules = User::getValidationRules();
        return [
            'student_id' => array_merge($rules['student_id'], ['unique:users']),
            'name' => $rules['name'],
            'name_yomi' => $rules['name_yomi'],
            'email' => array_merge($rules['email'], ['unique:users']),
            'univemail_local_part' => $rules['univemail_local_part'],
            'univemail_domain_part' => $rules['univemail_domain_part'],
            'tel' => $rules['tel'],
            'password' => array_merge($rules['password'], ['confirmed']),
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
            'student_id.unique' => '入力された' . config('portal.student_id_name') . 'はすでに登録されています',
            'email.unique' => '入力されたメールアドレスはすでに登録されています',
            'name.regex' => '姓と名の間にはスペースを入れてください',
            'name_yomi.regex' => '姓と名の間にはスペースを入れてください。また、ひらがなで記入してください',
            // ひらがなもカタカナも入力可能だが，説明が面倒なので，エラー上ではひらがなでの記入を促す
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (
                !User::isValidUnivemailByLocalPartAndDomainPart(
                    $this->univemail_local_part,
                    $this->univemail_domain_part
                )
            ) {
                $validator->errors()->add('univemail', '不正なメールアドレスです。');
            }
        });
    }
}
