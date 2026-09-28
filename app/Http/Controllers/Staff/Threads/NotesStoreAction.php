<?php

namespace App\Http\Controllers\Staff\Threads;

use App\Eloquents\Thread;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Threads\PostNoteRequest;
use App\Services\Threads\ThreadsService;
use Illuminate\Support\Facades\Auth;

class NotesStoreAction extends Controller
{
    /**
     * @var ThreadsService
     */
    private $threadsService;

    public function __construct(ThreadsService $threadsService)
    {
        $this->threadsService = $threadsService;
    }

    public function __invoke(Thread $thread, PostNoteRequest $request)
    {
        $values = $request->validated();

        $this->threadsService->postInternalNote(
            $thread,
            Auth::user(),
            $values['body'],
            $request->file('attachments', []),
            $values['client_token']
        );

        return redirect()
            ->route('staff.threads.show', ['thread' => $thread])
            ->with('topAlert.title', '内部メモを追加しました');
    }
}
