<?php

namespace Tests\Feature\Services\Threads;

use App\Eloquents\Circle;
use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use App\Eloquents\User;
use App\Exceptions\Threads\StaleThreadException;
use App\Services\Threads\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThreadsServiceTest extends TestCase
{
    use RefreshDatabase;

    private ThreadsService $threadsService;
    private Circle $circle;
    private User $member;
    private User $staff;

    public function setUp(): void
    {
        parent::setUp();

        $this->threadsService = App::make(ThreadsService::class);
        $this->circle = factory(Circle::class)->create();
        $this->member = factory(User::class)->create();
        $this->circle->users()->attach($this->member->id, ['is_leader' => true]);
        $this->staff = factory(User::class)->states('staff')->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 企画の会話は初回取得時に作成され2回目以降は同じ会話が返る()
    {
        $thread1 = $this->threadsService->getOrCreateForCircle($this->circle);
        $thread2 = $this->threadsService->getOrCreateForCircle($this->circle);

        $this->assertSame($thread1->id, $thread2->id);
        $this->assertDatabaseCount('threads', 1);
        $this->assertSame(Thread::STATUS_RESOLVED, $thread1->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 企画に所属していないユーザーの会話は本人専用で作成される()
    {
        $user = factory(User::class)->create();

        $thread = $this->threadsService->getOrCreateForUser($user);

        $this->assertNull($thread->circle_id);
        $this->assertSame($user->id, $thread->user_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 個人の会話はDBでもユーザーごとに一件に制限する()
    {
        $user = factory(User::class)->create();
        $this->threadsService->getOrCreateForUser($user);

        $this->expectException(QueryException::class);
        Thread::create([
            'circle_id' => null,
            'user_id' => $user->id,
            'status' => Thread::STATUS_RESOLVED,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 企画側からのメッセージは常にneeds_staffにする()
    {
        $thread = $this->threadsService->getOrCreateForCircle($this->circle);

        $this->threadsService->postCircleMessage(
            $thread,
            $this->member,
            'こんにちは',
            null,
            [],
            (string)Str::uuid()
        );

        $thread->refresh();
        $this->assertSame(Thread::STATUS_NEEDS_STAFF, $thread->status);
        $this->assertNotNull($thread->last_entry_at);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function スタッフのメッセージで返答待ちや解決済みに遷移し企画側の返信でneeds_staffへ戻る()
    {
        $thread = $this->threadsService->getOrCreateForCircle($this->circle);
        $this->threadsService->postCircleMessage($thread, $this->member, '質問です', null, [], (string)Str::uuid());

        $this->threadsService->postStaffMessage(
            $thread,
            $this->staff,
            '回答です',
            Thread::STATUS_AWAITING_REPLY,
            [],
            (string)Str::uuid()
        );
        $thread->refresh();
        $this->assertSame(Thread::STATUS_AWAITING_REPLY, $thread->status);

        $this->threadsService->postCircleMessage($thread, $this->member, '追加の質問です', null, [], (string)Str::uuid());
        $thread->refresh();
        $this->assertSame(Thread::STATUS_NEEDS_STAFF, $thread->status);

        $this->threadsService->postStaffMessage(
            $thread,
            $this->staff,
            '解決しました',
            Thread::STATUS_RESOLVED,
            [],
            (string)Str::uuid()
        );
        $thread->refresh();
        $this->assertSame(Thread::STATUS_RESOLVED, $thread->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 内部メモは状態を変更しない()
    {
        $thread = $this->threadsService->getOrCreateForCircle($this->circle);
        $this->threadsService->postCircleMessage($thread, $this->member, '質問です', null, [], (string)Str::uuid());
        $thread->refresh();
        $statusBefore = $thread->status;

        $entry = $this->threadsService->postInternalNote($thread, $this->staff, '内部メモです', [], (string)Str::uuid());

        $thread->refresh();
        $this->assertSame($statusBefore, $thread->status);
        $this->assertSame(ThreadEntry::KIND_INTERNAL_NOTE, $entry->kind);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 同じclient_tokenでの二重送信は1件しか作られない()
    {
        $thread = $this->threadsService->getOrCreateForCircle($this->circle);
        $token = (string)Str::uuid();

        $entry1 = $this->threadsService->postCircleMessage($thread, $this->member, '本文', null, [], $token);
        $entry2 = $this->threadsService->postCircleMessage($thread, $this->member, '本文', null, [], $token);

        $this->assertSame($entry1->id, $entry2->id);
        $this->assertTrue($entry1->wasRecentlyCreated);
        $this->assertFalse($entry2->wasRecentlyCreated);
        $this->assertDatabaseCount('thread_entries', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 担当者を設定できる()
    {
        $thread = $this->threadsService->getOrCreateForCircle($this->circle);

        $updated = $this->threadsService->setAssignee($thread, $this->staff, $thread->lock_version);

        $this->assertSame($this->staff->id, $updated->assignee_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 古いlock_versionでの担当者設定はStaleThreadExceptionになる()
    {
        $thread = $this->threadsService->getOrCreateForCircle($this->circle);

        $this->expectException(StaleThreadException::class);
        $this->threadsService->setAssignee($thread, $this->staff, $thread->lock_version + 1);
    }
}
