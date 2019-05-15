<?php

namespace App\Http\Requests\Users;

use App\Eloquents\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * @property-read string $password
 * @property-read string $new_password
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 現在のパスワード
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
            // 新しいパスワード
            'new_password' => array_merge(User::PASSWORD_RULES, ['confirmed']),
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
            'password' => 'パスワード',
            'new_password' => '新しいパスワード',
        ];
    }
}
