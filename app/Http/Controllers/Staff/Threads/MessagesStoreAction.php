<?php

namespace App\Http\Controllers\Staff\Threads;

use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Threads\PostMessageRequest;
use App\Mail\Threads\StaffMessageMailable;
use App\Services\Threads\ThreadsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MessagesStoreAction extends Controller
{
    /**
     * @var ThreadsService
     */
    private $threadsService;

    public function __construct(ThreadsService $threadsService)
    {
        $this->threadsService = $threadsService;
    }

    public function __invoke(Thread $thread, PostMessageRequest $request)
    {
        $values = $request->validated();

        $entry = $this->threadsService->postStaffMessage(
            $thread,
            Auth::user(),
            $values['body'],
            $values['target_status'],
            $request->file('attachments', []),
            $values['client_token']
        );

        if ($entry->wasRecentlyCreated) {
            $this->notifyCircle($thread, $entry);
        }

        return redirect()
            ->route('staff.threads.show', ['thread' => $thread])
            ->with('topAlert.title', 'メッセージを送信しました');
    }

    /**
     * 企画のメンバー（または企画に所属していない送信者本人）へ返信を通知する
     *
     * @param Thread $thread
     * @param ThreadEntry $entry
     * @return void
     */
    private function notifyCircle(Thread $thread, ThreadEntry $entry): void
    {
        $recipients = !empty($thread->circle_id) ? $thread->circle->users : collect([$thread->user])->filter();

        foreach ($recipients as $recipient) {
            Mail::to($recipient)
                ->send(
                    (new StaffMessageMailable($thread, $entry))
                        ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                        ->subject('お問い合わせに返信がありました')
                );
        }
    }
}
