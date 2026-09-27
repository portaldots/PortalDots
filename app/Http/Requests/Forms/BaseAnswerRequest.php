<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Services\Forms\AnswerInputNormalizer;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Answer;
use App\Eloquents\Circle;

abstract class BaseAnswerRequest extends FormRequest implements AnswerRequestInterface
{
    private $validationRulesService;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    abstract public function authorize();

    /**
     * Get data to be validated from the request.
     *
     * @return array
     */
    public function validationData()
    {
        $all = $this->all();

        if (array_key_exists('answers', $all)) {
            $form = $this->route('form');
            if ($form) {
                $answer = $this->route('answer');
                $all['answers'] = App::make(AnswerInputNormalizer::class)
                    ->normalize($all['answers'], $form, $answer instanceof Answer ? $answer : null);
            }
        }

        return $all;
    }

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
