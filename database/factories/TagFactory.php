<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Tag;
use Faker\Generator as Faker;

$factory->define(Tag::class, function (Faker $faker) {
    return [
        // 同じnameが2つ以上生成されないよう、乱数を追加する
        'name' => $faker->name . strval(mt_rand(0, 10000)),
    ];
});
