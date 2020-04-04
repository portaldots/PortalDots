<?php

namespace App\Http\Requests\Circles;

use App\Eloquents\Circle;
use Illuminate\Foundation\Http\FormRequest;

class SendEmailsRequest extends FormRequest
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
            'recipient' => ['required'],
            'title' => ['required'],
            'message' => ['required'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'recipient' => '宛先',
            'title' => '件名',
            'message' => '本文',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'required' => ':attribute は必須項目です',
        ];
    }
}
