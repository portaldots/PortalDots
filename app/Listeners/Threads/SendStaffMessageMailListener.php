<?php

declare(strict_types=1);

namespace App\Listeners\Threads;

use App\Contracts\ThreadReplyAddress;
use App\Events\Threads\StaffMessagePosted;
use App\Mail\Threads\StaffMessageMailable;
use App\Services\Notifications\NotificationDeliveryService;
use Illuminate\Support\Facades\Mail;

/**
 * スタッフからの会話への返信を、企画のメンバー（または企画に所属していない
 * 送信者本人）へ通知メールで送信する
 */
class SendStaffMessageMailListener
{
    private NotificationDeliveryService $notificationDeliveryService;
    private ThreadReplyAddress $threadReplyAddress;

    public function __construct(
        NotificationDeliveryService $notificationDeliveryService,
        ThreadReplyAddress $threadReplyAddress
    ) {
        $this->notificationDeliveryService = $notificationDeliveryService;
        $this->threadReplyAddress = $threadReplyAddress;
    }

    public function handle(StaffMessagePosted $event): void
    {
        $thread = $event->thread;
        $entry = $event->entry;

        $recipients = !empty($thread->circle_id) ? $thread->circle->users : collect([$thread->user])->filter();

        foreach ($recipients as $recipient) {
            $dedupeKey = "staff-message-posted:{$entry->id}:{$recipient->id}";
            $replyAddress = $this->threadReplyAddress->for($thread, $recipient);

            $send = function () use ($recipient, $thread, $entry, $replyAddress) {
                $mailable = (new StaffMessageMailable($thread, $entry))
                    ->subject('お問い合わせに返信がありました');
                if ($replyAddress !== null) {
                    $mailable->replyTo($replyAddress);
                } else {
                    $mailable->replyTo(config('portal.contact_email'), config('portal.admin_name'));
                }
                Mail::to($recipient)->send($mailable);
            };
            $this->notificationDeliveryService->sendOnce($dedupeKey, $recipient->id, $send);
        }
    }
}
