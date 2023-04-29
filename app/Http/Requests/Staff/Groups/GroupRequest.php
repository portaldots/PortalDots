<?php

namespace App\Http\Requests\Staff\Groups;

use App\Eloquents\Group;
use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
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
            'name' => '団体名',
            'name_yomi' => '団体名(よみ)',
            'notes' => 'スタッフ用メモ',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = Group::getValidationRules(isIndividual: false);
        return [
            'name' => $rules['name'],
            'name_yomi' => $rules['name_yomi'],
            'notes' => ['nullable'],
        ];
    }
}
