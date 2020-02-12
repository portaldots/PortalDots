<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Circle;

abstract class BaseAnswerRequest extends FormRequest
{
    private $validationRulesService;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(ValidationRulesService $validationRulesService)
    {
        return $validationRulesService->getRulesFromForm($this->route('form'), $this);
    }

    public function attributes()
    {
        $validationRulesService = App::make(ValidationRulesService::class);
        return $validationRulesService->getAttributesFromForm($this->route('form'))->toArray();
    }
}
