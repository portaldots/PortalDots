<?php

namespace App\Http\Requests\Staff\Documents;

use Illuminate\Foundation\Http\FormRequest;
use App;
use Illuminate\Support\Arr;

class UpdateDocumentRequest extends FormRequest
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
        $rules = App::make(CreateDocumentRequest::class)->rules();
        Arr::forget($rules, 'file');
        return $rules;
    }

    /**
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        return App::make(CreateDocumentRequest::class)->attributes();
    }

    public function messages()
    {
        return App::make(CreateDocumentRequest::class)->messages();
    }
}
