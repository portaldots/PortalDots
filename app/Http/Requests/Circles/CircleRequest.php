<?php

namespace App\Http\Requests\Circles;

use App\Eloquents\Circle;
use App\Eloquents\ParticipationType;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\Forms\ValidationRulesService;
use App\Http\Requests\Forms\AnswerRequestInterface;
use Illuminate\Support\Facades\App;

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
            'participation_type' => ['required', 'integer', 'exists:participation_types,id'],
            'name' => Circle::NAME_RULES,
            'name_yomi' => Circle::NAME_YOMI_RULES,
            'group_name' => Circle::GROUP_NAME_RULES,
            'group_name_yomi' => Circle::GROUP_NAME_YOMI_RULES,
        ];

        $custom_form_rules = $validationRulesService->getRulesFromForm(
            ParticipationType::findOrFail($this->participation_type)->form,
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
            'participation_type' => '参加種別',
            'name' => '企画名',
            'name_yomi' => '企画名(よみ)',
            'group_name' => '企画を出店する団体の名称',
            'group_name_yomi' => '企画を出店する団体の名称(よみ)',
        ];

        $validationRulesService = App::make(ValidationRulesService::class);
        $custom_form_attributes = $validationRulesService->getAttributesFromForm(
            ParticipationType::findOrFail($this->participation_type)->form
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
            'name_yomi.regex' => 'ひらがなで入力してください',
            'group_name_yomi.regex' => 'ひらがなで入力してください',
            // ひらがなもカタカナも入力可能だが，説明が面倒なので，エラー上ではひらがなでの入力を促す
        ];
    }
}
