<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Place;
use Faker\Generator as Faker;

$factory->define(Place::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'type' => $faker->numberBetween(1, 3),
    ];
});
