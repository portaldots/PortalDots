<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\CustomForm;
use App\Eloquents\Form;
use Faker\Generator as Faker;

$factory->define(CustomForm::class, function (Faker $faker) {
    return [
        'type' => 'circle',
        'form_id' => function() {
            return factory(Form::class)->create()->id;
        },
    ];
});
