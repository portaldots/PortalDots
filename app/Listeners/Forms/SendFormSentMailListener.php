<?php

declare(strict_types=1);

namespace App\Listeners\Forms;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Events\Forms\FormSent;
use App\Mail\Forms\FormSentMailable;
use App\Services\Notifications\NotificationDeliveryService;
use App\Services\Utils\FormatTextService;
use Illuminate\Support\Facades\Mail;

/**
 * 企画へ送付されたフォームについて、企画のメンバーへ通知メールを送信する
 */
class SendFormSentMailListener
{
    private NotificationDeliveryService $notificationDeliveryService;

    public function __construct(NotificationDeliveryService $notificationDeliveryService)
    {
        $this->notificationDeliveryService = $notificationDeliveryService;
    }

    public function handle(FormSent $event): void
    {
        $circle = Circle::with('users')->find($event->circleId);
        $form = Form::find($event->formId);
        if (empty($circle) || empty($form)) {
            return;
        }

        $answer = Answer::where('form_id', $form->id)->where('circle_id', $circle->id)->first();
        $url = !empty($answer)
            ? route('forms.answers.edit', ['form' => $form->id, 'answer' => $answer->id])
            : route('forms.answers.create', ['form' => $form->id]);

        $dueAtText = empty($event->dueAt) ? '指定なし' : FormatTextService::datetime($event->dueAt);

        foreach ($circle->users as $recipient) {
            $dedupeKey = "form-sent:{$event->assignmentId}:{$recipient->id}";

            $send = function () use ($recipient, $event, $dueAtText, $url) {
                Mail::to($recipient)
                    ->send(
                        (new FormSentMailable($event->formName, $dueAtText, $url))
                            ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                            ->subject("「{$event->formName}」が送付されました")
                    );
            };
            $this->notificationDeliveryService->sendOnce($dedupeKey, $recipient->id, $send);
        }
    }
}
