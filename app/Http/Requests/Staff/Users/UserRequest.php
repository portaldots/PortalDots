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
                [Rule::unique('users')->ignore($this->route('user'))]
            ),
            'name' => User::NAME_RULES,
            'name_yomi' => User::NAME_YOMI_RULES,
            'email' => array_merge(User::EMAIL_RULES, [Rule::unique('users')->ignore($this->route('user'))]),
            'tel' => User::TEL_RULES,
            'user_type' => Rule::in(['normal', 'staff', 'admin']),
            'notes' => ['nullable'],
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
            'user_type' => 'ユーザー種別',
            'notes' => 'スタッフ用メモ',
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
        $user = $this->route('user');
        $validator->after(function ($validator) use ($user) {
            if (!empty($this->user_type)) {
                if (Auth::id() === $user->id) {
                    $validator->errors()->add(
                        'user_type',
                        '自分自身の「ユーザー種別」を変更することはできません。'
                    );
                } elseif (!Auth::user()->is_admin && $user->is_admin) {
                    $validator->errors()->add(
                        'user_type',
                        '「ユーザー種別」が「管理者」のユーザーを「スタッフ」または「一般ユーザー」に変更するには、あなた自身が「管理者」である必要があります。'
                    );
                } elseif (!Auth::user()->is_admin && $this->user_type === 'admin') {
                    $validator->errors()->add(
                        'user_type',
                        '「ユーザー種別」を「管理者」に変更するためには、あなた自身が「管理者」である必要があります。'
                    );
                }
            } elseif (
                Auth::id() !== $user->id &&
                ((!Auth::user()->is_admin && !$user->is_admin) || Auth::user()->is_admin)
            ) {
                // user_type が empty であることを許容しないのは、
                // 変更対象のユーザーと自分自身が一致していない時、かつ以下のいずれかの時
                // - 変更対象のユーザー、自分自身、ともに管理者ではない時
                // - 自分自身が管理者である時
                $validator->errors()->add('user_type', 'ユーザー種別を選んでください。');
            }
        });
    }
}
