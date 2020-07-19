<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Page;
use App\Eloquents\User;
use Faker\Generator as Faker;

$factory->define(Page::class, function (Faker $faker) {
    return [
        'title' => $faker->name,
        'body' => $faker->text,
        'created_by' => function() {
            return factory(User::class)->create()->id;
        },
        'updated_by' => function() {
            return factory(User::class)->create()->id;
        },
    ];
});
