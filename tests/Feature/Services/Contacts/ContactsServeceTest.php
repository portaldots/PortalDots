<?php

namespace Tests\Feature\Services\Contacts;

use App\Eloquents\Thread;
use App\Mail\Contacts\ContactMailable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Contacts\ContactsService;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;
use App;
use App\Eloquents\Circle;
use App\Eloquents\ContactCategory;
use App\Eloquents\User;

class ContactsServeceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ContactsService
     */
    private $contactsService;

        /**
     * @var Circle
     */
    private $circle;

    /**
     * @var User
     */
    private $leader;

    /**
     * @var User
     */
    private $member;

    /**
     * @var ContactCategory
     */
    private $contactCategory;

    public function setUp(): void
    {
        parent::setUp();
        $this->contactsService = App::make(ContactsService::class);
        $this->circle = factory(Circle::class)->create();
        $this->leader = factory(User::class)->create();
        $this->member = factory(User::class)->create();

        $this->circle->users()->attach([
            $this->leader->id => ['is_leader' => true],
            $this->member->id,
        ]);

        $this->contactCategory = factory(ContactCategory::class)->create();
    }

    private function create()
    {
        Mail::fake();

        $this->contactsService->create($this->circle, $this->leader, "こんにちは。\nこれはてすとです。", $this->contactCategory);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function send_お問い合わせが企画のメンバーに送信できる()
    {
        $this->create();

        Mail::assertSent(ContactMailable::class, function ($mail) {
            return $mail->hasTo($this->leader->email);
        });

        Mail::assertSent(ContactMailable::class, function ($mail) {
            return $mail->hasTo($this->member->email);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sendToStaff_スタッフ用控えが送信できる()
    {
        $this->create();

        Mail::assertSent(ContactMailable::class, function ($mail) {
            return $mail->hasTo($this->contactCategory->email);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 同じ企画へ2回お問い合わせすると会話に2件記録され状態がneeds_staffになる()
    {
        Mail::fake();

        $this->contactsService->create($this->circle, $this->leader, '1回目の本文', $this->contactCategory);
        $this->contactsService->create($this->circle, $this->leader, '2回目の本文', $this->contactCategory);

        $thread = Thread::where('circle_id', $this->circle->id)->firstOrFail();
        $this->assertSame(Thread::STATUS_NEEDS_STAFF, $thread->status);
        $this->assertSame(2, $thread->entries()->count());
        $this->assertDatabaseCount('answers', 0);

        // 1回ごとに メンバー2人 + スタッフ控え = 3通、2回で6通
        Mail::assertSent(ContactMailable::class, 6);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 送信が途中で失敗しても同じトークンで再送信すると未送信の宛先にだけ届く()
    {
        // スタッフ用控えは企画のメンバー全員へ送った後に送るため、控えで失敗させると
        // 1回目で送信済みになった宛先が再送信で飛ばされることを確かめられる
        $failingEmail = $this->contactCategory->email;
        Event::listen(MessageSending::class, function (MessageSending $event) use (&$failingEmail) {
            foreach ($event->message->getTo() as $address) {
                if ($address->getAddress() === $failingEmail) {
                    throw new RuntimeException('SMTP failure');
                }
            }
        });

        try {
            $this->contactsService->create(
                $this->circle,
                $this->leader,
                '本文',
                $this->contactCategory,
                [],
                'retry-token'
            );
            $this->fail('1回目の送信は失敗するはず');
        } catch (RuntimeException $e) {
            $this->assertSame('SMTP failure', $e->getMessage());
        }

        $failingEmail = null;
        $this->contactsService->create(
            $this->circle,
            $this->leader,
            '本文',
            $this->contactCategory,
            [],
            'retry-token'
        );

        $sentTo = collect(Mail::mailer()->getSymfonyTransport()->messages())
            ->flatMap(fn ($sent) => $sent->getEnvelope()->getRecipients())
            ->map(fn ($address) => $address->getAddress())
            ->countBy()
            ->all();
        $this->assertSame(1, $sentTo[$this->leader->email] ?? 0);
        $this->assertSame(1, $sentTo[$this->member->email] ?? 0);
        $this->assertSame(1, $sentTo[$this->contactCategory->email] ?? 0);

        $thread = Thread::where('circle_id', $this->circle->id)->firstOrFail();
        $this->assertSame(1, $thread->entries()->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 企画に所属していないユーザーがお問い合わせすると本人専用の会話が作られる()
    {
        Mail::fake();
        $user = factory(User::class)->create();

        $this->contactsService->create(null, $user, 'こんにちは', $this->contactCategory);

        $thread = Thread::whereNull('circle_id')->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(Thread::STATUS_NEEDS_STAFF, $thread->status);
        $this->assertSame(1, $thread->entries()->count());
    }
}
