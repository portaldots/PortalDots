<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Eloquents\Document;
use App\Eloquents\DocumentVersion;
use Faker\Generator as Faker;

$factory->define(DocumentVersion::class, function (Faker $faker) {
    return [
        'document_id' => function () {
            return factory(Document::class)->create()->id;
        },
        'version' => 1,
        'path' => 'documents/foobar.pdf',
        'size' => 1,
        'extension' => 'pdf',
        'uploaded_by' => null,
    ];
});
