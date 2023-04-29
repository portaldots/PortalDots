<?php

namespace App\Http\Controllers\Staff\Groups\SendEmails;

use App\Eloquents\Group;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Groups\SendEmailsRequest;
use App\Services\Emails\SendEmailService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SendAction extends Controller
{
    public $sendEmailService;

    public function __construct(SendEmailService $sendEmailService)
    {
        $this->sendEmailService = $sendEmailService;
    }

    public function __invoke(Group $group, SendEmailsRequest $request)
    {
        if ($request->recipient === 'all') {
            $recipients = $group->users()->get();
        } else {
            $recipients = $group->owner()->get();
        }

        if ($recipients->isEmpty()) {
            return redirect()
                ->route('staff.groups.email', ['group' => $group])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '宛先が存在しないため送信できませんでした')
                ->withInput();
        }

        $this->sendEmailService->bulkEnqueue(
            $request->subject,
            $request->body,
            $recipients
        );

        $this->sendEmailService->bulkEnqueue(
            '【スタッフ用控え】' . $request->subject,
            $request->body,
            new Collection([Auth::user()])
        );

        return redirect()->route('staff.groups.email', ['group' => $group])
            ->with('topAlert.title', "件名：「{$request->subject}」を送信予約しました")
            ->with('topAlert.body', 'メールを送信するのに時間がかかる場合があります。');
    }
}
