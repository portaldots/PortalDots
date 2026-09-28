<?php

namespace App\Http\Controllers\Documents\Approvals;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\RespondsToStaleDocumentApproval;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Exceptions\Documents\StaleDocumentApprovalException;
use App\Http\Requests\Documents\RequestChangesDocumentApprovalRequest;
use App\Services\Circles\SelectorService;
use App\Services\Documents\DocumentApprovalsService;

class RequestChangesAction extends Controller
{
    use RespondsToStaleDocumentApproval;

    /**
     * @var SelectorService
     */
    private $selectorService;

    /**
     * @var DocumentApprovalsService
     */
    private $documentApprovalsService;

    public function __construct(SelectorService $selectorService, DocumentApprovalsService $documentApprovalsService)
    {
        $this->selectorService = $selectorService;
        $this->documentApprovalsService = $documentApprovalsService;
    }

    public function __invoke(Document $document, RequestChangesDocumentApprovalRequest $request)
    {
        $circle = $this->selectorService->getCircle();

        $isVisible = Document::whereKey($document->id)
            ->visibleTo(Auth::user(), $circle)
            ->exists();

        if (!$isVisible) {
            abort(404);
        }

        $approval = DocumentApproval::where('document_id', $document->id)
            ->where('circle_id', $circle->id)
            ->first();

        if (empty($approval)) {
            abort(404);
        }

        $this->authorize('decide', $approval);

        $values = $request->validated();

        try {
            $this->documentApprovalsService->decide(
                $approval,
                Auth::user(),
                DocumentApproval::STATUS_CHANGES_REQUESTED,
                $values['comment'],
                (int)$values['document_version_id'],
                (int)$values['lock_version']
            );
        } catch (StaleDocumentApprovalException $e) {
            return $this->staleDocumentApprovalResponse($request);
        }

        return redirect()
            ->route('documents.approval.show', ['document' => $document])
            ->with('topAlert.title', '修正を依頼しました');
    }
}
