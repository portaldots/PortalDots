<?php

namespace App\Http\Requests\Circles;

use App;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\Forms\ValidationRulesService;
use App\Http\Requests\Forms\AnswerRequestInterface;

class CircleRequest extends FormRequest implements AnswerRequestInterface
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
    public function rules(ValidationRulesService $validationRulesService)
    {
        $rules = [
            'name' => Circle::NAME_RULES,
            'name_yomi' => Circle::NAME_YOMI_RULES,
            'group_name' => Circle::GROUP_NAME_RULES,
            'group_name_yomi' => Circle::GROUP_NAME_YOMI_RULES,
        ];

        $custom_form_rules = $validationRulesService->getRulesFromForm(
            CustomForm::getFormByType('circle'),
            $this
        );

        return \array_merge($rules, $custom_form_rules);
    }

    /**
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        $attributes = [
            'name' => '企画の名前',
            'name_yomi' => '企画の名前(よみ)',
            'group_name' => '企画団体の名前',
            'group_name_yomi' => '企画団体の名前(よみ)',
        ];

        $validationRulesService = App::make(ValidationRulesService::class);
        $custom_form_attributes = $validationRulesService->getAttributesFromForm(
            CustomForm::getFormByType('circle')
        )->toArray();

        return \array_merge($attributes, $custom_form_attributes);
    }

    /**
     * バリデーションエラーメッセージ取得
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name_yomi.regex' => 'ひらがなで記入してください',
            'group_name_yomi.regex' => 'ひらがなで記入してください',
            // ひらがなもカタカナも入力可能だが，説明が面倒なので，エラー上ではひらがなでの記入を促す
        ];
    }
}
