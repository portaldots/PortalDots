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
    foreach (['admin', 'user'] as $index => $login) {
        $users[] = User::forceCreate([
            'student_id' => 'DEMO' . ($index + 1),
            'name' => 'Demo ' . ucfirst($login),
            'name_yomi' => 'でも ゆーざー',
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
    foreach (['Music', 'Art'] as $index => $name) {
        $tag = Tag::forceCreate(['name' => $name]);
        $circle = Circle::forceCreate([
            'name' => $name . ' Cafe', 'name_yomi' => 'でもかふぇ',
            'group_name' => $name . ' Club', 'group_name_yomi' => 'でもくらぶ',
            'submitted_at' => now(), 'status' => 'approved',
        ]);
        $circle->tags()->attach($tag);
        $circle->users()->attach($users[0], ['is_leader' => true]);
        $circle->users()->attach($users[1], ['is_leader' => false]);
        Page::forceCreate([
            'title' => $name . ' festival information',
            'body' => 'Welcome to the browser demo. Changes stay in this tab until it is closed or restarted.',
            'is_public' => true, 'is_pinned' => $index === 0,
        ]);
        $form = Form::forceCreate([
            'name' => $name . ' registration', 'description' => 'Seeded demonstration form.',
            'open_at' => now()->subDay(), 'close_at' => now()->addYear(),
            'type' => 'circle', 'max_answers' => 1, 'is_public' => true,
        ]);
        $form->questions()->create(['type' => 'text', 'name' => 'Project name', 'priority' => 1]);
    }
});
echo "Seeded admin@example.test / user@example.test; password PortalDemo123!\n";
