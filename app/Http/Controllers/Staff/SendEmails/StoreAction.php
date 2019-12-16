<?php

namespace App\Http\Controllers\Staff\SendEmails;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Emails\SendEmailService;
use App\Eloquents\Page;
use App\Eloquents\User;

class StoreAction extends Controller
{
    /**
     * @var SendEmailService
     */
    public $sendEmailService;

    public function __construct(SendEmailService $sendEmailService)
    {
        $this->sendEmailService = $sendEmailService;
    }

    public function __invoke(Request $request)
    {
        $page = Page::findOrFail((int)$request->page_id);
        $users = User::verified()->get();
        $this->sendEmailService->bulkEnqueue(
            $page->title,
            $page->body,
            $users
        );

        return redirect()
            ->route('staff.send_emails')
            ->with('toast', '一斉メール送信の予約が完了しました');
    }
}
