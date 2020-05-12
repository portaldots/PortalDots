<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Services\Forms\QuestionsService;
use App\Http\Requests\Staff\Forms\Editor\UpdateQuestionRequest;
use App\Http\Controllers\Controller;

class UpdateQuestionAction extends Controller
{
    private $questionsService;

    public function __construct(QuestionsService $questionsService)
    {
        $this->questionsService = $questionsService;
    }

    // TODO: is_required は null にできないよ！的な SQL エラーが発生しないようにする
    public function __invoke(Form $form, UpdateQuestionRequest $request)
    {
        $question_id = (int)$request->question['id'];
        $question = $request->question;
        unset($question['created_at'], $question['updated_at'], $question['id']);

        $this->questionsService->updateQuestion(
            $question_id,
            $question
        );
    }
}
