<?php

namespace App\Http\Requests\Staff\Circles;

use Illuminate\Foundation\Http\FormRequest;

class TermsRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
        ];
    }

    public function attributes()
    {
        return [
            'description' => ['参加登録前に表示する内容'],
        ];
    }
}
