<?php
require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $exception): never {
    fwrite(STDERR, (string)$exception . "\n");
    exit(1);
});
if (App\Eloquents\User::count() !== 2 || App\Eloquents\Form::count() !== 2) {
    throw new RuntimeException('Seed counts failed');
}
if (App\Eloquents\Page::byKeywords('festival information')->count() !== 2
    || App\Eloquents\Page::byKeywords('missing-keyword')->count() !== 0) {
    throw new RuntimeException('SQLite keyword search failed');
}
$grid = $app->make(App\GridMakers\FormsGridMaker::class);
foreach ([
    ['name', '!=', 'Music registration', 1],
    ['created_at', '<', '2000-01-01 00:00', 0],
    ['created_at', '>=', '2000-01-01 00:00', 2],
] as [$key, $operator, $value, $expected]) {
    $filters = App\GridMakers\Filter\FilterQueries::fromArray([
        ['key_name' => $key, 'operator' => $operator, 'value' => $value],
    ]);
    if ($grid->getCount($filters, 'and') !== $expected) {
        throw new RuntimeException('SQLite grid filter failed: ' . $key . ' ' . $operator);
    }
}
Illuminate\Support\Facades\DB::beginTransaction();
try {
    $form = App\Eloquents\Form::firstOrFail();
    $checkbox = $form->questions()->create(['type' => 'checkbox', 'name' => 'Choices', 'priority' => 2]);
    $answer = App\Eloquents\Answer::create([
        'form_id' => $form->id, 'circle_id' => App\Eloquents\Circle::firstOrFail()->id,
    ]);
    foreach (['Alpha', 'Beta'] as $value) {
        App\Eloquents\AnswerDetail::forceCreate([
            'answer_id' => $answer->id, 'question_id' => $checkbox->id, 'answer' => $value,
        ]);
    }
    $query = App\GridMakers\Helpers\AnswerDetailsHelper::makeQueryWithAnswerDetails(
        App\Eloquents\Answer::query()->select('answers.id', 'pivot_answer_details.*'),
        $form->questions()->get(), 'question_', "\n"
    );
    $choices = explode("\n", $query->firstOrFail()->{'question_' . $checkbox->id});
    sort($choices);
    if ($choices !== ['Alpha', 'Beta']) {
        throw new RuntimeException('SQLite checkbox aggregation failed');
    }
} finally {
    Illuminate\Support\Facades\DB::rollBack();
}
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::create('/login'));
if ($response->getStatusCode() !== 200 || strpos($response->getContent(), 'login_id') === false) {
    throw new RuntimeException('SQLite login render failed: ' . $response->getContent());
}
echo "Laravel boot + SQLite seed counts, keyword search, grid inequality/datetime filters, checkbox aggregation, login render: OK\n";
