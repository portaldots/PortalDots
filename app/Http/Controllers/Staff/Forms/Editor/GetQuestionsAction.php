<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Eloquents\CustomForm;
use App\Eloquents\Option;
use App\Eloquents\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetQuestionsAction extends Controller
{
    public function __invoke(Form $form)
    {
        $questions = $form->questions()->get();
        $questions_on_db = $questions->map(function (Question $question) {
            return [
                'id' => $question->id,
                'name' => $question->name,
                'description' => $question->description,
                'type' => $question->type,
                'is_required' => $question->is_required,
                'number_min' => $question->number_min,
                'number_max' => $question->number_max,
                'allowed_types' => $question->allowed_types,
                'options' => $question->options,
                'priority' => $question->priority,
                'created_at' => $question->created_at,
                'updated_at' => $question->updated_at,
            ];
        })->toArray();

        $permanent_questions = [];
        $custom_form = $form->customForm()->first();
        $type = isset($custom_form) ? $custom_form->type : null;
        if (isset($type)) {
            $permanent_questions = CustomForm::getPermanentQuestionsDict()[$type];
        }

        return \array_merge($permanent_questions, $questions_on_db);
    }
}
