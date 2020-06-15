<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\ContactEmail;
use Faker\Generator as Faker;

$factory->define(ContactEmail::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email
    ];
});
