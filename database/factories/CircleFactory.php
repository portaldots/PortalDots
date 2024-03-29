<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Circle;
use App\Eloquents\ParticipationType;
use Faker\Generator as Faker;

$factory->define(Circle::class, function (Faker $faker) {
    return [
        'participation_type_id' => function () {
            return ParticipationType::factory()->create()->id;
        },
        'name' => $faker->name,
        'name_yomi' => $faker->kanaName,
        'group_name' => $faker->name,
        'group_name_yomi' => $faker->kanaName,
        'submitted_at' => now(),
        'status' => 'approved'
    ];
});

$factory->state(Circle::class, 'rejected', [
    'status' => 'rejected',
]);

$factory->state(Circle::class, 'notSubmitted', [
    'submitted_at' => null,
    'status' => null,
    'invitation_token' => bin2hex(random_bytes(16)),
]);
