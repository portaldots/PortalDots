<?php

namespace App\Http\Controllers\Contacts\Attachments;

use App\Eloquents\ThreadEntry;
use App\Eloquents\ThreadEntryAttachment;
use App\Http\Controllers\Controller;
use App\Services\Threads\ThreadAttachmentsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ShowAction extends Controller
{
    /**
     * @var ThreadAttachmentsService
     */
    private $threadAttachmentsService;

    public function __construct(ThreadAttachmentsService $threadAttachmentsService)
    {
        $this->threadAttachmentsService = $threadAttachmentsService;
    }

    public function __invoke(ThreadEntryAttachment $attachment)
    {
        $entry = $attachment->threadEntry;
        if (empty($entry) || $entry->kind === ThreadEntry::KIND_INTERNAL_NOTE) {
            // 内部メモの添付ファイルは企画側には非公開
            abort(404);
        }

        $thread = $entry->thread;
        if (empty($thread) || Gate::forUser(Auth::user())->denies('view', $thread)) {
            abort(404);
        }

        $path = $this->threadAttachmentsService->getPath($attachment);
        abort_if($path === null, 404);

        return response()->download($path, $attachment->name);
    }
}
