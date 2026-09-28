<?php

declare(strict_types=1);

namespace App\Listeners\Forms;

use App\Eloquents\Circle;
use App\Events\Forms\AnswerReturned;
use App\Mail\Forms\AnswerReturnedMailable;
use App\Services\Notifications\NotificationDeliveryService;
use Illuminate\Support\Facades\Mail;

/**
 * 差し戻された回答について、企画のメンバーへ通知メールを送信する
 */
class SendAnswerReturnedMailListener
{
    private NotificationDeliveryService $notificationDeliveryService;

    public function __construct(NotificationDeliveryService $notificationDeliveryService)
    {
        $this->notificationDeliveryService = $notificationDeliveryService;
    }

    public function handle(AnswerReturned $event): void
    {
        $circle = Circle::with('users')->find($event->circleId);
        if (empty($circle)) {
            return;
        }

        $url = route('forms.answers.edit', ['form' => $event->formId, 'answer' => $event->answerId]);

        foreach ($circle->users as $recipient) {
            $dedupeKey = "answer-returned:{$event->answerId}:{$event->lockVersion}:{$recipient->id}";

            $send = function () use ($recipient, $event, $url) {
                Mail::to($recipient)
                    ->send(
                        (new AnswerReturnedMailable($event->formName, $event->reason, $url))
                            ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                            ->subject("「{$event->formName}」の回答を差し戻しました")
                    );
            };
            $this->notificationDeliveryService->sendOnce($dedupeKey, $recipient->id, $send);
        }
    }
}
