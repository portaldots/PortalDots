<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Services\Forms\QuestionsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdateQuestionAction extends Controller
{
    private $questionsService;

    public function __construct(QuestionsService $questionsService)
    {
        $this->questionsService = $questionsService;
    }

    // TODO: ちゃんとバリデーションする
    // TODO: is_required は null にできないよ！的な SQL エラーが発生しないようにする
    public function __invoke(int $form_id, Request $request)
    {
        $form = Form::withoutGlobalScope('withoutCustomForms')->findOrFail($form_id);
        $question_id = (int)$request->question['id'];
        $question = $request->question;
        unset($question['created_at'], $question['updated_at'], $question['id']);

        $this->questionsService->updateQuestion(
            $question_id,
            $question
        );
    }
}
