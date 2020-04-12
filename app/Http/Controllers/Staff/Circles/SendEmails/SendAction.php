<?php

namespace App\Http\Controllers\Staff\Circles\SendEmails;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\SendEmailsRequest;
use App\Services\Emails\SendEmailService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SendAction extends Controller
{
    public $sendEmailService;

    public function __construct(SendEmailService $sendEmailService)
    {
        $this->sendEmailService = $sendEmailService;
    }

    public function __invoke(Circle $circle, SendEmailsRequest $request)
    {
        if ($request->recipient === 'all') {
            $recipients = $circle->users()->get();
            $body = "-----\n企画名: **{$circle->name}**\n\nこのメッセージは企画責任者・副責任者に送信されています\n\n-----\n";
        } else {
            $recipients = $circle->leader()->get();
            $body = "-----\n企画名: **{$circle->name}**\n\nこのメッセージは企画責任者のみに送信されています\n\n-----\n";
        }

        if ($recipients->isEmpty()) {
            return redirect()
                ->route('staff.circles.email', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '宛先が存在しないため送信できませんでした')
                ->withInput();
        }

        $body .= $request->body;

        $this->sendEmailService->bulkEnqueue(
            $request->subject,
            $body,
            $recipients
        );

        $this->sendEmailService->bulkEnqueue(
            '【スタッフ用控え】' . $request->subject,
            $body,
            new Collection([Auth::user()])
        );

        return redirect()->route('staff.circles.email', ['circle' => $circle])
            ->with('topAlert.title', "件名：「{$request->subject}」を送信予約しました")
            ->with('topAlert.body', 'メールを送信するのに時間がかかる場合があります。');
    }
}
