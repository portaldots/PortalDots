<?php

namespace App\Http\Controllers\Staff\Threads\Attachments;

use App\Eloquents\ThreadEntryAttachment;
use App\Http\Controllers\Controller;
use App\Services\Threads\ThreadAttachmentsService;

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
        $path = $this->threadAttachmentsService->getPath($attachment);
        abort_if($path === null, 404);

        return response()->file($path);
    }
}
