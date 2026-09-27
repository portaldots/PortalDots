<?php

namespace App\Http\Controllers\Staff\Threads;

use App\Eloquents\Thread;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Threads\PostMessageRequest;
use App\Services\Threads\ThreadsService;
use Illuminate\Support\Facades\Auth;

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

        $this->threadsService->postStaffMessage(
            $thread,
            Auth::user(),
            $values['body'],
            $values['target_status'],
            $request->file('attachments', []),
            $values['client_token']
        );

        return redirect()
            ->route('staff.threads.show', ['thread' => $thread])
            ->with('topAlert.title', 'メッセージを送信しました');
    }
}
