<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Document;
use Faker\Generator as Faker;

$factory->define(Document::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->text,
        'path' => 'documents/foobar.pdf',
        'size' => 1,
        'extension' => 'pdf',
        'created_by' => 1,
        'updated_by' => 1,
        'is_public' => true,
        'is_important' => false,
        'schedule_id' => null,
        'notes' => $faker->text,
    ];
});
