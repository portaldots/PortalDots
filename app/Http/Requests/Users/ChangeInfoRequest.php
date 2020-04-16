<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Eloquents\User;
use Illuminate\Support\Facades\Auth;

class ChangeInfoRequest extends FormRequest
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
            'student_id' => array_merge(
                User::STUDENT_ID_RULES,
                ['sometimes', Rule::unique('users')->ignore(Auth::user()),
                    ]
            ),
            'name' => User::NAME_RULES,
            'name_yomi' => User::NAME_YOMI_RULES,
            'email' => array_merge(User::TEL_RULES, [Rule::unique('users')->ignore(Auth::user())]),
            'tel' => User::TEL_RULES,
            'password' => array_merge(User::PASSWORD_RULES, [
                // 現在のパスワードが正しいものか検証する
                function ($attribute, $value, $fail) {
                    /** @var User $user */
                    $user = Auth::user();
                    if (! Auth::attempt(['login_id' => $user->email, 'password' => $value])) {
                        $fail('パスワードが違います。');
                    }
                }
            ]),
        ];
    }

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

    public function withValidator($validator)
    {
        $user = Auth::user();
        $circles = $user->circles()->get();
        if (!empty($circles)) {
            $validator->after(function ($validator) use ($user) {
                if (!empty($this->name) && $this->name !== $user->name) {
                    $validator->errors()->add('name', '企画に所属しているか、参加登録の途中のため修正できません');
                }

                if (!empty($this->name_yomi) && $this->name_yomi !== $user->name_yomi) {
                    $validator->errors()->add('name_yomi', '企画に所属しているか、参加登録の途中のため修正できません');
                }

                if (!empty($this->student_id) && $this->student_id !== $user->student_id) {
                    $validator->errors()->add('student_id', '企画に所属しているか、参加登録の途中のため修正できません');
                }
            });
        }
    }
}
