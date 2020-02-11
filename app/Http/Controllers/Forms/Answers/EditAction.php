<?php

namespace App\Http\Controllers\Forms\Answers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Services\Forms\AnswerDetailsService;

class EditAction extends Controller
{
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        // 他団体の回答を編集できないようにする
        $this->middleware('can:update,answer');

        $this->answerDetailsService = $answerDetailsService;
    }

    public function __invoke(Form $form, Answer $answer)
    {
        $questions = $form->questions()->get();
        return view('v2.forms.answers.form')
            ->with('circle', $answer->circle)
            ->with('form', $form)
            ->with('questions', $questions)
            ->with('answer', $answer)
            ->with('answer_details', $this->answerDetailsService->getAnswerDetailsByAnswer($answer));
    }
}
