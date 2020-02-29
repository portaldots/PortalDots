<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\AnswerDetail;
use App\Eloquents\Answer;
use App\Eloquents\Question;
use Faker\Generator as Faker;

$factory->define(AnswerDetail::class, function (Faker $faker) {
    return [
        'answer_id' => function() {
            return factory(Answer::class)->create()->id;
        },
        'question_id' => function() {
            return factory(Question::class)->create()->id;
        },
        'answer' => $faker->paragraph(),
    ];
});
