<?php

namespace App\Http\Controllers\Staff\Documents\Approvals;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Http\Controllers\Controller;
use App\Services\Documents\DocumentApprovalsService;

class DestroyAction extends Controller
{
    /**
     * @var DocumentApprovalsService
     */
    private $documentApprovalsService;

    public function __construct(DocumentApprovalsService $documentApprovalsService)
    {
        $this->documentApprovalsService = $documentApprovalsService;
    }

    public function __invoke(Document $document, Circle $circle)
    {
        $this->documentApprovalsService->cancelRequest($document, $circle);

        return redirect()
            ->route('staff.documents.edit', ['document' => $document])
            ->with('topAlert.title', '確認依頼を取り消しました');
    }
}
