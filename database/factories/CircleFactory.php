<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Circle;
use Faker\Generator as Faker;

$factory->define(Circle::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'created_by' => 1,
        'updated_by' => 1,
    ];
});
