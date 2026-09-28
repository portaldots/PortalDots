<?php

namespace App\Http\Controllers\Forms\Answers;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Answer;
use App\Eloquents\AnswerRevision;
use App\Services\Forms\AnswerDetailsService;

class RevisionsShowAction extends Controller
{
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        // 他企画の回答を閲覧できないようにする
        $this->middleware('can:view,answer');

        $this->answerDetailsService = $answerDetailsService;
    }

    public function __invoke(Form $form, Answer $answer, AnswerRevision $revision)
    {
        if (!$form->requires_review || (int)$form->id !== (int)$answer->form_id) {
            abort(404);
            return;
        }

        $circle = $answer->circle()->approved()->firstOrFail();

        return view('forms.answers.revision')
            ->with('circle', $circle)
            ->with('form', $form)
            ->with('questions', $form->questions()->get())
            ->with('answer', $answer)
            ->with('revision', $revision)
            ->with('answer_details', $this->answerDetailsService->getAnswerDetailsByRevision($form, $revision));
    }
}
