<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Question;
use App\Eloquents\Form;
use Faker\Generator as Faker;

$options = <<< EOL
Option A
Option B
Option C
Option D
Other
EOL;

$factory->define(Question::class, function (Faker $faker) use ($options) {
    static $priority = 0;

    $type = $faker->randomElement([
                'heading',
                'text',
                'textarea',
                'number',
                'radio',
                'select',
                'checkbox',
                'upload',
            ]);

    return [
        'form_id' => function() {
            return factory(Form::class)->create()->id;
        },
        'name' => $faker->name,
        'description' => $faker->text,
        'type' => $type,
        'is_required' => $faker->boolean,
        'number_min' => mt_rand(0, 40),
        'number_max' => mt_rand(50, 100),
        'allowed_types' => ($type === 'upload' ? 'png|jpg|jpeg|gif' : null),
        'options' => (in_array($type, ['radio', 'select', 'checkbox'], true) ? $options : null),
        'priority' => ++$priority,
    ];
});
