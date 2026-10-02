<?php

namespace Tests\Feature\Listeners\Threads;

use App\Contracts\ThreadReplyAddress;
use App\Eloquents\Circle;
use App\Eloquents\Thread;
use App\Eloquents\User;
use App\Mail\Threads\StaffMessageMailable;
use App\Services\Threads\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

/**
 * ThreadReplyAddress の既定実装（null固定）と差し替えの両方で、スタッフからの
 * 返信メールのReply-Toが期待通りになることを確認する
 */
class SendStaffMessageMailListenerTest extends TestCase
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
    public function 既定のThreadReplyAddressではReply_Toがお問い合わせ用メールアドレスのまま変わらない()
    {
        Mail::fake();

        $this->threadsService->postStaffMessage(
            $this->thread,
            $this->staff,
            '回答です',
            Thread::STATUS_AWAITING_REPLY,
            [],
            (string)Str::uuid()
        );

        Mail::assertSent(StaffMessageMailable::class, function ($mail) {
            return $mail->hasTo($this->member->email)
                && $mail->hasReplyTo(config('portal.contact_email'), config('portal.admin_name'));
        });
        Mail::assertSent(StaffMessageMailable::class, function ($mail) {
            return $mail->hasTo($this->secondMember->email)
                && $mail->hasReplyTo(config('portal.contact_email'), config('portal.admin_name'));
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 会話の更新が取り消された場合はメールを送らない()
    {
        Mail::fake();

        try {
            DB::transaction(function () {
                $this->threadsService->postStaffMessage(
                    $this->thread,
                    $this->staff,
                    '送信しない返信',
                    Thread::STATUS_AWAITING_REPLY,
                    [],
                    (string)Str::uuid()
                );
                Mail::assertNothingSent();
                throw new RuntimeException('更新を取り消す');
            });
        } catch (RuntimeException $e) {
            $this->assertSame('更新を取り消す', $e->getMessage());
        }

        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ThreadReplyAddressを差し替えると各受信者のメールに差し替え先が返したアドレスがReply_Toとして設定される()
    {
        Mail::fake();

        $calls = [];
        $this->mock(ThreadReplyAddress::class, function ($mock) use (&$calls) {
            $mock->shouldReceive('for')
                ->twice()
                ->andReturnUsing(function (Thread $thread, User $recipient) use (&$calls) {
                    $calls[] = [$thread->id, $recipient->id];
                    return "reply+{$thread->id}-{$recipient->id}@replies.example.com";
                });
        });

        $this->threadsService->postStaffMessage(
            $this->thread,
            $this->staff,
            '回答です',
            Thread::STATUS_AWAITING_REPLY,
            [],
            (string)Str::uuid()
        );

        $memberAddress = "reply+{$this->thread->id}-{$this->member->id}@replies.example.com";
        $secondMemberAddress = "reply+{$this->thread->id}-{$this->secondMember->id}@replies.example.com";

        Mail::assertSent(StaffMessageMailable::class, function ($mail) use ($memberAddress) {
            return $mail->hasTo($this->member->email) && $mail->hasReplyTo($memberAddress);
        });
        Mail::assertSent(StaffMessageMailable::class, function ($mail) use ($secondMemberAddress) {
            return $mail->hasTo($this->secondMember->email) && $mail->hasReplyTo($secondMemberAddress);
        });

        // 差し替え先が、各受信者について正しい会話とユーザーを受け取ったことを確認する
        $this->assertContains([$this->thread->id, $this->member->id], $calls);
        $this->assertContains([$this->thread->id, $this->secondMember->id], $calls);
    }
}
