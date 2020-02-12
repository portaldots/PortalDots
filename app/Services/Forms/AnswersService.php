<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Services\Forms\AnswerDetailsService;
use App\Http\Requests\Forms\BaseAnswerRequest;
use DB;

class AnswersService
{
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        $this->answerDetailsService = $answerDetailsService;
    }

    public function createAnswer(Form $form, Circle $circle, BaseAnswerRequest $request)
    {
        return DB::transaction(function () use ($form, $circle, $request) {
            $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

            $answer = Answer::create([
                'form_id' => $form->id,
                'circle_id' => $circle->id,
            ]);

            $this->answerDetailsService->updateAnswerDetails($form, $answer, $answer_details);

            return $answer;
        });
    }

    public function updateAnswer(Form $form, Answer $answer, BaseAnswerRequest $request)
    {
        return DB::transaction(function () use ($form, $answer, $request) {
            $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

            $answer->update();
            $this->answerDetailsService->updateAnswerDetails($form, $answer, $answer_details);

            return true;
        });
    }
}
