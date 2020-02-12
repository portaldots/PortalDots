<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Circle;

class UpdateAnswerRequest extends BaseAnswerRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $form = $this->route('form');
        $answer = $this->route('answer');
        return $this->user()->can('update', $answer) && $form->is_public && $form->isOpen();
    }
}
