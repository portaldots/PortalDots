<?php

namespace App\Http\Requests\Circles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AuthRequest extends FormRequest
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
            'password' => [function ($attribute, $value, $fail) {
                if (!Auth::attempt(['login_id' => Auth::user()->email, 'password' => $value])) {
                    return $fail('パスワードが違います。');
                }
            }]
        ];
    }
}
