<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Eloquents\Option;
use App\Eloquents\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetQuestionsAction extends Controller
{
    public function __invoke(Form $form)
    {
        $questions = $form->questions()->get();
        return $questions->map(function (Question $question) {
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
        });
    }
}
