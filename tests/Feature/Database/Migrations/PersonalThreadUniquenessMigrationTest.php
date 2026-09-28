<?php

namespace Tests\Feature\Database\Migrations;

use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalThreadUniquenessMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 既存の重複した個人会話を統合してから一意制約を作る()
    {
        $migration = require database_path('migrations/2026_09_28_070000_add_unique_personal_thread_user.php');
        $migration->down();

        $user = factory(User::class)->create();
        $first = Thread::create([
            'circle_id' => null, 'user_id' => $user->id, 'status' => Thread::STATUS_RESOLVED,
        ]);
        $second = Thread::create([
            'circle_id' => null, 'user_id' => $user->id, 'status' => Thread::STATUS_NEEDS_STAFF,
            'last_entry_at' => now(),
        ]);
        foreach ([$first, $second] as $thread) {
            $thread->entries()->create([
                'kind' => ThreadEntry::KIND_MESSAGE,
                'author_side' => ThreadEntry::AUTHOR_SIDE_CIRCLE,
                'body' => '残すメッセージ',
                'client_token' => 'same-token',
            ]);
        }

        $migration->up();

        $this->assertSame(1, Thread::where('user_id', $user->id)->count());
        $this->assertSame(2, ThreadEntry::where('thread_id', $first->id)->count());
        $this->assertSame(Thread::STATUS_NEEDS_STAFF, $first->fresh()->status);
        $this->assertNull(Thread::find($second->id));
    }
}
