<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Services\Forms\AnswerDetailsService;
use DB;

class AnswersService
{
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        $this->answerDetailsService = $answerDetailsService;
    }

    public function createAnswer(Form $form, Circle $circle, array $answer_details)
    {
        return DB::transaction(function () use ($form, $circle, $answer_details) {
            $answer = Answer::create([
                'form_id' => $form->id,
                'circle_id' => $circle->id,
            ]);

            $this->answerDetailsService->updateAnswerDetails($answer, $answer_details);

            return $answer;
        });
    }

    public function updateAnswer(Answer $answer, array $answer_details)
    {
        return DB::transaction(function () use ($answer, $answer_details) {
            $answer->update();
            $this->answerDetailsService->updateAnswerDetails($answer, $answer_details);

            return true;
        });
    }
}
