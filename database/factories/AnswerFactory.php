<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Answer;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use Faker\Generator as Faker;

$factory->define(Answer::class, function (Faker $faker) {
    return [
        'form_id' => function() {
            return factory(Form::class)->create()->id;
        },
        'circle_id' => function() {
            return factory(Circle::class)->create()->id;
        },
    ];
});
