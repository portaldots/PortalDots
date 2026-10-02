<?php

namespace App\Http\Requests\Forms;

use App;
use Gate;
use App\Services\Forms\ValidationRulesService;
use App\Eloquents\Answer;
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
        if (
            !$this->user()->can('update', $answer) || !$form->is_public ||
            !$form->isOpen() || $form->id !== $answer->form_id
        ) {
            return false;
        }

        if ($form->requires_review && $answer->review_status === Answer::REVIEW_STATUS_ACCEPTED) {
            // 完了済の回答は、スタッフが差し戻すまで企画側からは編集できない
            return false;
        }

        return true;
    }
}
