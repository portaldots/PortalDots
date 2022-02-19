<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Eloquents\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

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
        'univemail_local_part' => $faker->unique()->slug,
        'univemail_domain_part' => $faker->unique()->safeEmailDomain,
        'tel' => $faker->phoneNumber,
        'is_staff' => false,
        'is_admin' => false,
        'email_verified_at' => now(),
        'univemail_verified_at' => now(),
        'signed_up_at' => now(),
        'last_accessed_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ];
});

$factory->state(User::class, 'staff', [
    'is_staff' => true,
    'is_admin' => false,
]);

$factory->state(User::class, 'admin', [
    'is_staff' => true,
    'is_admin' => true,
]);

$factory->state(User::class, 'not_verified', [
    'email_verified_at' => null,
    'univemail_verified_at' => null,
    'signed_up_at' => null,
]);
