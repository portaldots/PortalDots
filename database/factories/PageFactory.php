<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Page;
use Faker\Generator as Faker;

$factory->define(Page::class, function (Faker $faker) {
    return [
        'title' => $faker->name,
        'body' => $faker->text,
        'is_pinned' => false,
        'is_public' => true,
    ];
});
