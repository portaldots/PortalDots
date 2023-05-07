<?php

namespace App\Http\Requests\Circles;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Forms\AnswerRequestInterface;

class SubmitRequest extends FormRequest implements AnswerRequestInterface
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
            'last_updated_timestamp' => ['required', 'integer'],
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
            'last_updated_timestamp' => '最終更新日時',
        ];
    }
}
