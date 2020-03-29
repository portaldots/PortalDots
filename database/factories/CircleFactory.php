<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Circle;
use Faker\Generator as Faker;

$factory->define(Circle::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'name_yomi' => $faker->kanaName,
        'group_name' => $faker->name,
        'group_name_yomi' => $faker->kanaName,
        'submitted_at' => now(),
        'status' => 'approved'
    ];
});
