<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Circle;

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
        $form = $this->route('form');
        return Gate::allows('circle.belongsTo', Circle::findOrFail($this->circle_id)) &&
            $form->is_public && $form->isOpen();
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
