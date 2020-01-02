<?php

namespace App\Http\Requests;

use App\Eloquents\Circle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->has('circle_id')) {
            return !empty($this->circle_id) && Gate::allows('circle.belongsTo', Circle::find($this->circle_id));
        }
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
            'circle_id' => 'filled',
            'contact_body' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'contact_body.required' => 'お問い合わせ内容は必ず入力してください',
        ];
    }
}
