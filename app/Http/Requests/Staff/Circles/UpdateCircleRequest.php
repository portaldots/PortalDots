<?php

namespace App\Http\Requests\Staff\Circles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCircleRequest extends FormRequest
{
    private CreateCircleRequest $createCircleRequest;

    public function __construct(CreateCircleRequest $createCircleRequest)
    {
        $this->createCircleRequest = $createCircleRequest;
    }

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
        $rules = $this->createCircleRequest->rules();
        unset($rules['participation_type_id']);
        return $rules;
    }

    /**
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        return $this->createCircleRequest->attributes();
    }

    public function messages()
    {
        return $this->createCircleRequest->messages();
    }

    public function withValidator($validator)
    {
        return $this->createCircleRequest->withValidator($validator);
    }
}
