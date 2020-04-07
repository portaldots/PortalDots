<?php

namespace App\Http\Controllers\Staff\Circles\SendEmails;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\SendEmailsRequest;
use App\Services\Emails\SendEmailService;
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
        if (count($circle->users()->get()) === 0) {
            return redirect()
                ->route('staff.circles.email', ['circle' => $circle])
                ->with('topAlert.title', '送信に失敗しました')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.body', 'この企画に登録されているユーザーが存在しません')
                ->withInput();
        }
        
        if ($request->recipient === 'all') {
            $recipients = $circle->users()->get();
            $body = "-----\n企画名: **{$circle->name}**\n\nこのメッセージは企画責任者・副責任者に送信されています\n\n-----\n";
        } else {
            $recipients = $circle->users()->wherePivot('is_leader', true)->get();
            $body = "-----\n企画名: **{$circle->name}**\n\nこのメッセージは企画責任者のみに送信されています\n\n-----\n";
        }

        $recipients->push(Auth::user());
        $body .= $request->body;

        $this->sendEmailService->bulkEnqueue(
            $request->subject,
            $body,
            $recipients
        );

        return redirect()->route('staff.circles.email', ['circle' => $circle])
            ->with('topAlert.title', "件名：「{$request->subject}」を {$circle->name} 宛で送信予約しました")
            ->with('topAlert.body', 'メールを送信するのに時間がかかる場合があります。');
    }
}
