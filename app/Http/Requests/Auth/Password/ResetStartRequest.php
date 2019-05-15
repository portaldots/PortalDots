<?php

namespace App\Http\Requests\Auth\Password;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $login_id
 */
class ResetStartRequest extends FormRequest
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
            'login_id' => ['required'],
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
            'login_id' => '学籍番号・連絡先メールアドレス',
        ];
    }
}
