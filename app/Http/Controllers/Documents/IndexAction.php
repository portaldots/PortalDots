<?php

namespace App\Http\Controllers\Documents;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;
use App\Services\Circles\SelectorService;

class IndexAction extends Controller
{
    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    public function __invoke()
    {
        $circle = $this->selectorService->getCircle();

        $documents = Document::visibleTo(Auth::user(), $circle)
            ->with('versions')
            ->with(['approvals' => function ($query) use ($circle) {
                $query->where('circle_id', $circle?->id);
            }])
            ->paginate(10);

        if ($documents->currentPage() > $documents->lastPage()) {
            return redirect($documents->url($documents->lastPage()));
        }

        return view('documents.index')
            ->with('documents', $documents);
    }
}
