<?php

namespace App\Http\Requests\Staff\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Eloquents\User;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
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
                [Rule::unique('users')->ignore(Auth::user())]
            ),
            'name' => User::NAME_RULES,
            'name_yomi' => User::NAME_YOMI_RULES,
            'email' => array_merge(User::EMAIL_RULES, [Rule::unique('users')->ignore(Auth::user())]),
            'tel' => User::TEL_RULES,
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
}
