<?php

namespace App\Http\Controllers\Staff\Documents\Approvals;

use App\Eloquents\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Documents\StoreDocumentApprovalRequest;
use App\Services\Documents\DocumentApprovalsService;
use Illuminate\Support\Facades\Auth;

class StoreAction extends Controller
{
    /**
     * @var DocumentApprovalsService
     */
    private $documentApprovalsService;

    public function __construct(DocumentApprovalsService $documentApprovalsService)
    {
        $this->documentApprovalsService = $documentApprovalsService;
    }

    public function __invoke(StoreDocumentApprovalRequest $request, Document $document)
    {
        $values = $request->validated();

        $this->documentApprovalsService->requestForCircles($document, $values['circles'], Auth::user());

        return redirect()
            ->route('staff.documents.edit', ['document' => $document])
            ->with('topAlert.title', '確認依頼を送信しました');
    }
}
