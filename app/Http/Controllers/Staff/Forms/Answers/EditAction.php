<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Services\Forms\AnswersService;
use App\Services\Forms\AnswerDetailsService;

class EditAction extends Controller
{
    private $answersService;
    private $answerDetailsService;

    public function __construct(
        AnswersService $answersService,
        AnswerDetailsService $answerDetailsService
    ) {
        $this->answersService = $answersService;
        $this->answerDetailsService = $answerDetailsService;
    }

    public function __invoke(Form $form, Answer $answer)
    {
        if ($form->id !== $answer->form_id) {
            abort(404);
            return;
        }

        $circle = $answer->circle()->submitted()->firstOrFail();
        return view('v2.staff.forms.answers.form')
            ->with('circle', $circle)
            ->with('form', $form)
            ->with('questions', $form->questions()->get())
            ->with('answers', $this->answersService->getAnswersByCircle($form, $circle))
            ->with('answer', $answer)
            ->with('answer_details', $this->answerDetailsService->getAnswerDetailsByAnswer($answer));
    }
}
