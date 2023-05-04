<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Eloquents\Question;
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
        if (isset($form->participationType)) {
            $permanent_questions = [
                [
                    'id' => 'circle.name',
                    'name' => '企画名',
                    'type' => 'text',
                    'is_required' => true,
                    'is_permanent' => true,
                ],
                [
                    'id' => 'circle.name_yomi',
                    'name' => '企画名(よみ)',
                    'type' => 'text',
                    'is_required' => true,
                    'is_permanent' => true,
                ],
                [
                    'id' => 'circle.group_name',
                    'name' => '企画を出店する団体の名称',
                    'type' => 'text',
                    'is_required' => true,
                    'is_permanent' => true,
                ],
                [
                    'id' => 'circle.group_name_yomi',
                    'name' => '企画を出店する団体の名称(よみ)',
                    'type' => 'text',
                    'is_required' => true,
                    'is_permanent' => true,
                ],
            ];
        }

        return \array_merge($permanent_questions, $questions_on_db);
    }
}
