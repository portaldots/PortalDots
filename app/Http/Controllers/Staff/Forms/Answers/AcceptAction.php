<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\RespondsToStaleAnswer;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Answer;
use App\Exceptions\Forms\StaleAnswerException;
use App\Services\Forms\AnswersService;

class AcceptAction extends Controller
{
    use RespondsToStaleAnswer;

    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, Answer $answer, Request $request)
    {
        if (!$form->requires_review || (int)$form->id !== (int)$answer->form_id) {
            abort(404);
        }

        $lockVersion = $request->has('lock_version') ? (int)$request->input('lock_version') : null;

        try {
            $this->answersService->acceptAnswer($answer, Auth::user(), $lockVersion);
        } catch (StaleAnswerException $e) {
            return $this->staleAnswerResponse($request);
        }

        return back()
            ->with('topAlert.title', '回答を完了にしました');
    }
}
