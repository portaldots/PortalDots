<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Answer;
use App\Eloquents\AnswerRevision;
use Faker\Generator as Faker;

$factory->define(AnswerRevision::class, function (Faker $faker) {
    return [
        'answer_id' => function () {
            return factory(Answer::class)->create()->id;
        },
        'revision' => 1,
        'details' => [],
        'submitted_by' => null,
        'submitted_at' => now(),
    ];
});
