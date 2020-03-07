<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Form;
use Faker\Generator as Faker;

$factory->define(Form::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->text,
        'open_at' => now()->subMonth(1),
        'close_at' => now()->addMonth(1),
        'created_by' => 1,
        'type' => 'circle',
        'max_answers' => 1,
        'is_public' => true,
    ];
});

$factory->state(Form::class, 'private', [
    'is_public' => false,
]);
