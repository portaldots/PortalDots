<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\ContactCategory;
use Faker\Generator as Faker;

$factory->define(ContactCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email
    ];
});
