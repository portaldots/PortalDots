<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Services\Forms\QuestionsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddQuestionAction extends Controller
{
    private $questionsService;

    public function __construct(QuestionsService $questionsService)
    {
        $this->questionsService = $questionsService;
    }

    public function __invoke(Form $form, Request $request)
    {
        $question = $this->questionsService->addQuestion($form, $request->type);
        return [
            'id' => $question->id,
            'name' => $question->name,
            'description' => $question->description,
            'type' => $question->type,
            'is_required' => $question->is_required,
            'number_min' => $question->number_min,
            'number_max' => $question->number_max,
            'allowed_types' => $question->allowed_types,
            'priority' => $question->priority,
            'created_at' => $question->created_at,
            'updated_at' => $question->updated_at,
        ];
    }
}
