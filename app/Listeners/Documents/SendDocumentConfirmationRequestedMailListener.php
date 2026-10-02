<?php

declare(strict_types=1);

namespace App\Listeners\Documents;

use App\Eloquents\Circle;
use App\Events\Documents\DocumentConfirmationRequested;
use App\Mail\Documents\DocumentConfirmationRequestedMailable;
use App\Services\Notifications\NotificationDeliveryService;
use Illuminate\Support\Facades\Mail;

/**
 * 配布資料の確認依頼について、企画のメンバーへ通知メールを送信する
 */
class SendDocumentConfirmationRequestedMailListener
{
    private NotificationDeliveryService $notificationDeliveryService;

    public function __construct(NotificationDeliveryService $notificationDeliveryService)
    {
        $this->notificationDeliveryService = $notificationDeliveryService;
    }

    public function handle(DocumentConfirmationRequested $event): void
    {
        $circle = Circle::with('users')->find($event->circleId);
        if (empty($circle)) {
            return;
        }

        $url = route('documents.approval.show', ['document' => $event->documentId]);

        foreach ($circle->users as $recipient) {
            $dedupeKey = "document-confirmation-requested:{$event->decisionId}:{$recipient->id}";

            $send = function () use ($recipient, $event, $url) {
                Mail::to($recipient)
                    ->send(
                        (new DocumentConfirmationRequestedMailable($event->documentName, $event->version, $url))
                            ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                            ->subject("「{$event->documentName}」の確認をお願いします")
                    );
            };
            $this->notificationDeliveryService->sendOnce($dedupeKey, $recipient->id, $send);
        }
    }
}
