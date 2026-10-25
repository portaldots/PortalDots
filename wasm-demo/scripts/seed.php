<?php

require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
set_exception_handler(static function (Throwable $exception): never {
    fwrite(STDERR, (string)$exception . "\n");
    exit(1);
});

use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Tag;
use App\Eloquents\Page;
use App\Eloquents\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

DB::transaction(function () {
    $users = [];
    $people = [
        'admin' => ['デモ スタッフ', 'でも すたっふ'],
        'user' => ['デモ 参加者', 'でも さんかしゃ'],
    ];
    foreach (array_keys($people) as $index => $login) {
        $users[] = User::forceCreate([
            'student_id' => 'DEMO' . ($index + 1),
            'name' => $people[$login][0],
            'name_yomi' => $people[$login][1],
            'email' => $login . '@example.test',
            'univemail_local_part' => 'DEMO' . ($index + 1),
            'univemail_domain_part' => 'example.test',
            'tel' => '000-0000-0000',
            'password' => Hash::make('PortalDemo123!'),
            'is_staff' => $login === 'admin',
            'is_admin' => $login === 'admin',
            'email_verified_at' => now(),
            'univemail_verified_at' => now(),
            'signed_up_at' => now(),
        ]);
    }
    $projects = [
        [
            'tag' => '音楽',
            'circle' => ['軽音楽部ライブ', 'けいおんがくぶらいぶ', '軽音楽部', 'けいおんがくぶ'],
            'page' => ['出店説明会のお知らせ', '出店説明会を11月1日（土）13時から講堂で開きます。各企画の責任者は出席してください。'],
            'form' => ['電源使用申請', '使用する機器と消費電力を入力してください。', '使用する機器'],
        ],
        [
            'tag' => '展示',
            'circle' => ['美術部作品展', 'びじゅつぶさくひんてん', '美術部', 'びじゅつぶ'],
            'page' => ['搬入スケジュールのお知らせ', '搬入は前日の17時から20時までです。台車は実行委員会本部で貸し出します。'],
            'form' => ['物品貸出申請', '借りたい物品と数を入力してください。', '借りたい物品'],
        ],
    ];
    foreach ($projects as $index => $project) {
        $tag = Tag::forceCreate(['name' => $project['tag']]);
        [$name, $nameYomi, $groupName, $groupNameYomi] = $project['circle'];
        $circle = Circle::forceCreate([
            'name' => $name, 'name_yomi' => $nameYomi,
            'group_name' => $groupName, 'group_name_yomi' => $groupNameYomi,
            'submitted_at' => now(), 'status' => 'approved',
        ]);
        $circle->tags()->attach($tag);
        $circle->users()->attach($users[0], ['is_leader' => true]);
        $circle->users()->attach($users[1], ['is_leader' => false]);
        Page::forceCreate([
            'title' => $project['page'][0],
            'body' => $project['page'][1],
            'is_public' => true, 'is_pinned' => $index === 0,
        ]);
        [$formName, $description, $question] = $project['form'];
        $form = Form::forceCreate([
            'name' => $formName, 'description' => $description,
            'open_at' => now()->subDay(), 'close_at' => now()->addYear(),
            'type' => 'circle', 'max_answers' => 1, 'is_public' => true,
        ]);
        $form->questions()->create(['type' => 'text', 'name' => $question, 'priority' => 1]);
    }
});
echo "Seeded admin@example.test / user@example.test; password PortalDemo123!\n";
