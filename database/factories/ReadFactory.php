<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Page;
use App\Eloquents\Read;
use App\Eloquents\User;
use Faker\Generator as Faker;

$factory->define(Read::class, function (Faker $faker) {
    return [
        'page_id' => function () {
            return factory(Page::class)->create()->id;
        },
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
    ];
});
