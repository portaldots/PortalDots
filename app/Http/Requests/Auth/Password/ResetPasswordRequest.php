<?php

namespace App\Http\Requests\Auth\Password;

use App\Eloquents\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $new_password
 */
class ResetPasswordRequest extends FormRequest
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
            'new_password' => '新しいパスワード',
        ];
    }
}
