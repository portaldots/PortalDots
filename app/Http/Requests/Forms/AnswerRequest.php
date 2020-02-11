<?php

namespace App\Http\Requests\Forms;

use App;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\Forms\ValidationRulesService;

class AnswerRequest extends FormRequest
{
    private $validationRulesService;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO: フォームの回答権限チェックや、受付期間チェックもやる
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(ValidationRulesService $validationRulesService)
    {
        return $validationRulesService->getRulesFromForm($this->route('form'));
    }

    public function attributes()
    {
        $validationRulesService = App::make(ValidationRulesService::class);
        return $validationRulesService->getAttributesFromForm($this->route('form'))->toArray();
    }
}
