<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use DB;

class AnswersService
{
    public function createAnswer(Form $form, Circle $circle, array $answer_details)
    {
        $rules = $form->questions->map(function ($question) {
        });

        DB::transaction(function () use ($form, $circle, $answer_details) {
            $answer = Answer::create([
                'form_id' => $form->id,
                'circle_id' => $circle->id,
            ]);
        });
    }
}
