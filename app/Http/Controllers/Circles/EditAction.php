<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Services\Forms\AnswerDetailsService;
use Illuminate\Support\Facades\Auth;

class EditAction extends Controller
{
    private $answerDetailsService;

    public function __construct(
        AnswerDetailsService $answerDetailsService
    ) {
        $this->answerDetailsService = $answerDetailsService;
    }

    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        if (!Auth::user()->isLeaderInCircle($circle)) {
            abort(403);
        }

        $answer = $circle->participationType->form->answers[0] ?? null;

        return view('circles.form')
            ->with('participation_type', $circle->participationType)
            ->with('circle', $circle)
            ->with('form', $circle->participationType->form)
            ->with('questions', $circle->participationType->form->questions)
            ->with('answer', $answer)
            ->with('answer_details', $this->answerDetailsService->getAnswerDetailsByAnswer($answer));
    }
}
