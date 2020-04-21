<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Circle;
use App\Services\Forms\AnswersService;

class StoreAnswerRequest extends BaseAnswerRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $answersService = App::make(AnswersService::class);
        $form = $this->route('form');
        $circle = Circle::approved()->findOrFail($this->circle_id);
        $answers = $answersService->getAnswersByCircle($form, $circle);
        return Gate::allows('circle.belongsTo', $circle) &&
            $form->is_public && $form->isOpen() && $form->max_answers > count($answers);
    }
}
