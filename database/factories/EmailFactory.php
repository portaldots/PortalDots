<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Email;
use Faker\Generator as Faker;

$factory->define(Email::class, function (Faker $faker) {
    return [
        'subject' => $faker->text,
        'body' => $faker->text,
        'email_to' => $faker->email,
        'email_to_name' => $faker->name,
        'locked_at' => null,
        'sent_at' => null,
        'count_failed' => 0,
    ];
});
