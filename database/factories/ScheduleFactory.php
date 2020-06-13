<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Schedule;
use Faker\Generator as Faker;

$factory->define(Schedule::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'start_at' => now()->subMonth(1),
        'place' => '講義棟101教室',
        'description' => $faker->text,
    ];
});
