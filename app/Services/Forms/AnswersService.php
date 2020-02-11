<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use DB;

class AnswersService
{
    public function createAnswer(Form $form, Circle $circle, array $answer_details)
    {
        return DB::transaction(function () use ($form, $circle, $answer_details) {
            $answer = Answer::create([
                'form_id' => $form->id,
                'circle_id' => $circle->id,
            ]);

            $data = [];
            foreach ($answer_details as $question_id => $detail) {
                if (is_array($detail)) {
                    foreach ($detail as $value) {
                        $data[] = [
                            'answer_id' => $answer->id,
                            'question_id' => $question_id,
                            'answer' => $value
                        ];
                    }
                } else {
                    $data[] = [
                        'answer_id' => $answer->id,
                        'question_id' => $question_id,
                        'answer' => $detail
                    ];
                }
            }

            AnswerDetail::insert($data);

            return true;
        });
    }
}
