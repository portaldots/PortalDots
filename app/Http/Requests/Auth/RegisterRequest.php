<?php

namespace App\Http\Requests\Auth;

use App\Eloquents\Group;
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
        $groupRules = Group::getValidationRules((bool)$this->request->getBoolean('is_individual', true));
        $userRules = User::getValidationRules();
        return [
            'is_individual' => ['required', 'boolean'],
            'group_name' => $groupRules['name'],
            'group_name_yomi' => $groupRules['name_yomi'],
            'student_id' => array_merge($userRules['student_id'], ['unique:users']),
            'name' => $userRules['name'],
            'name_yomi' => $userRules['name_yomi'],
            'email' => array_merge($userRules['email'], ['unique:users']),
            'univemail_local_part' => $userRules['univemail_local_part'],
            'univemail_domain_part' => $userRules['univemail_domain_part'],
            'tel' => $userRules['tel'],
            'password' => array_merge($userRules['password'], ['confirmed']),
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
            'is_individual' => '登録種別',
            'group_name' => '団体名',
            'group_name_yomi' => '団体名(よみ)',
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
            'is_individual.boolean' => '登録種別を選択してください。',
            'group_name_yomi.regex' => 'ひらがなで記入してください。',
            'student_id.unique' => '入力された' . config('portal.student_id_name') . 'はすでに登録されています。',
            'email.unique' => '入力されたメールアドレスはすでに登録されています。',
            'name.regex' => '姓と名の間にはスペースを入れてください。',
            'name_yomi.regex' => '姓と名の間にはスペースを入れてください。また、ひらがなで記入してください。',
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
