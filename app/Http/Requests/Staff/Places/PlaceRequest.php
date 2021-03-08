<?php

namespace App\Http\Requests\Staff\Places;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceRequest extends FormRequest
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
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => '場所名',
            'type' => 'タイプ',
            'notes' => 'スタッフ用メモ'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', Rule::unique('places')->ignore($this->place ?? null)],
            'type' => ['required', Rule::in([1, 2, 3])],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => '同名の場所が既に存在します。',
        ];
    }
}
