<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Eloquents\User;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'student_id' => Str::random(mt_rand(7, 20)),
        'name' => $faker->name,
        'name_yomi' => $faker->kanaName,
        'email' => $faker->unique()->safeEmail,
        'tel' => $faker->phoneNumber,
        'is_staff' => false,
        'email_verified_at' => now(),
        'univemail_verified_at' => now(),
        'is_signed_up' => true,
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

$factory->state(User::class, 'not_verified', [
    'email_verified_at' => null,
    'univemail_verified_at' => null,
]);
