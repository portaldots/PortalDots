<?php

namespace App\Http\Controllers\Documents\Approvals;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Services\Circles\SelectorService;

class ShowAction extends Controller
{
    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    public function __invoke(Document $document)
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
            ->with(['documentVersion', 'decisions.documentVersion', 'decisions.decidedBy'])
            ->first();

        if (empty($approval)) {
            abort(404);
        }

        return view('documents.approval.show')
            ->with('document', $document)
            ->with('approval', $approval);
    }
}
