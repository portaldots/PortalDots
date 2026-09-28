<?php

declare(strict_types=1);

namespace App\Listeners\Documents;

use App\Eloquents\Circle;
use App\Events\Documents\DocumentConfirmationReset;
use App\Mail\Documents\DocumentConfirmationResetMailable;
use App\Services\Notifications\NotificationDeliveryService;
use Illuminate\Support\Facades\Mail;

/**
 * 新しい版の追加による確認待ちへのリセットについて、企画のメンバーへ通知メールを送信する
 */
class SendDocumentConfirmationResetMailListener
{
    private NotificationDeliveryService $notificationDeliveryService;

    public function __construct(NotificationDeliveryService $notificationDeliveryService)
    {
        $this->notificationDeliveryService = $notificationDeliveryService;
    }

    public function handle(DocumentConfirmationReset $event): void
    {
        $circle = Circle::with('users')->find($event->circleId);
        if (empty($circle)) {
            return;
        }

        $url = route('documents.approval.show', ['document' => $event->documentId]);

        foreach ($circle->users as $recipient) {
            $dedupeKey = "document-confirmation-reset:{$event->decisionId}:{$recipient->id}";

            $send = function () use ($recipient, $event, $url) {
                Mail::to($recipient)
                    ->send(
                        (new DocumentConfirmationResetMailable($event->documentName, $event->version, $url))
                            ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                            ->subject("「{$event->documentName}」が確認待ちに戻りました")
                    );
            };
            $this->notificationDeliveryService->sendOnce($dedupeKey, $recipient->id, $send);
        }
    }
}
