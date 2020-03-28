<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Services\Forms\AnswerDetailsService;

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

        $form = CustomForm::getFormByType('circle');
        $answer = $circle->getCustomFormAnswer();
        return view('v2.circles.form')
            ->with('circle', $circle)
            ->with('form', $form)
            ->with('questions', $form->questions()->get())
            ->with('answer', $answer)
            ->with('answer_details', $this->answerDetailsService->getAnswerDetailsByAnswer($answer));
    }
}
