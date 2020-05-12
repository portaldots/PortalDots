<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Services\Forms\QuestionsService;
use App\Http\Requests\Staff\Forms\Editor\DeleteQuestionRequest;
use App\Http\Controllers\Controller;

class DeleteQuestionAction extends Controller
{
    private $questionsService;

    public function __construct(QuestionsService $questionsService)
    {
        $this->questionsService = $questionsService;
    }

    public function __invoke(Form $form, DeleteQuestionRequest $request)
    {
        $question_id = (int)$request->question;
        $this->questionsService->deleteQuestion($question_id);
    }
}
