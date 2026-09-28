<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use Auth;
use App\Http\Controllers\Concerns\RespondsToStaleAnswer;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Answer;
use App\Exceptions\Forms\StaleAnswerException;
use App\Http\Requests\Staff\Forms\AnswerReturnRequest;
use App\Services\Forms\AnswersService;

class ReturnAction extends Controller
{
    use RespondsToStaleAnswer;

    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, Answer $answer, AnswerReturnRequest $request)
    {
        if (!$form->requires_review || (int)$form->id !== (int)$answer->form_id) {
            abort(404);
        }

        $values = $request->validated();
        $lockVersion = isset($values['lock_version']) ? (int)$values['lock_version'] : null;

        try {
            $this->answersService->returnAnswer($answer, Auth::user(), $values['review_note'], $lockVersion);
        } catch (StaleAnswerException $e) {
            return $this->staleAnswerResponse($request);
        }

        return back()
            ->with('topAlert.title', '回答を差し戻しました');
    }
}
