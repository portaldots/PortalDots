<?php

namespace Tests\Feature\Http\Controllers\Staff\Threads;

use App\Eloquents\Circle;
use App\Eloquents\Permission;
use App\Eloquents\Thread;
use App\Eloquents\User;
use App\Mail\Threads\StaffMessageMailable;
use App\Services\Threads\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessagesStoreActionTest extends TestCase
{
    use RefreshDatabase;

    private ThreadsService $threadsService;
    private Circle $circle;
    private User $member;
    private User $secondMember;
    private User $staff;
    private Thread $thread;

    public function setUp(): void
    {
        parent::setUp();

        $this->threadsService = App::make(ThreadsService::class);
        $this->circle = factory(Circle::class)->create();
        $this->member = factory(User::class)->create();
        $this->circle->users()->attach($this->member->id, ['is_leader' => true]);
        $this->secondMember = factory(User::class)->create();
        $this->circle->users()->attach($this->secondMember->id, ['is_leader' => false]);
        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.threads.read,edit']);
        $this->staff->syncPermissions(['staff.threads.read,edit']);

        $this->thread = $this->threadsService->getOrCreateForCircle($this->circle);
        $this->threadsService->postCircleMessage(
            $this->thread,
            $this->member,
            'ご質問があります',
            null,
            [],
            (string)Str::uuid()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 送信して返答待ちにするボタンで状態が変わり企画のメンバー全員に1通ずつメールが送信される()
    {
        Mail::fake();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.threads.messages.store', ['thread' => $this->thread]), [
                'body' => 'ご質問への回答です',
                'target_status' => Thread::STATUS_AWAITING_REPLY,
                'client_token' => (string)Str::uuid(),
            ]);

        $response->assertRedirect(route('staff.threads.show', ['thread' => $this->thread]));
        $this->thread->refresh();
        $this->assertSame(Thread::STATUS_AWAITING_REPLY, $this->thread->status);

        Mail::assertSent(StaffMessageMailable::class, function ($mail) {
            return $mail->hasTo($this->member->email);
        });
        Mail::assertSent(StaffMessageMailable::class, function ($mail) {
            return $mail->hasTo($this->secondMember->email);
        });
        Mail::assertSent(StaffMessageMailable::class, 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 送信して解決済みにするボタンで状態が解決済みになる()
    {
        Mail::fake();

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.threads.messages.store', ['thread' => $this->thread]), [
                'body' => '対応完了しました',
                'target_status' => Thread::STATUS_RESOLVED,
                'client_token' => (string)Str::uuid(),
            ]);

        $this->thread->refresh();
        $this->assertSame(Thread::STATUS_RESOLVED, $this->thread->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 同じclient_tokenでの二重送信は1件のエントリと1通のメールしか作らない()
    {
        Mail::fake();
        $params = [
            'body' => '回答です',
            'target_status' => Thread::STATUS_AWAITING_REPLY,
            'client_token' => 'duplicate-token',
        ];

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.threads.messages.store', ['thread' => $this->thread]), $params);
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.threads.messages.store', ['thread' => $this->thread]), $params);

        // 企画の初回メッセージ + スタッフの返信1件 = 2件
        $this->assertDatabaseCount('thread_entries', 2);
        // 企画のメンバー2人に1通ずつ。2回目の投稿では重複送信しない
        Mail::assertSent(StaffMessageMailable::class, 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がない場合は送信できない()
    {
        $noPermissionStaff = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($noPermissionStaff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.threads.messages.store', ['thread' => $this->thread]), [
                'body' => '回答です',
                'target_status' => Thread::STATUS_AWAITING_REPLY,
                'client_token' => (string)Str::uuid(),
            ]);

        $response->assertForbidden();
    }
}
