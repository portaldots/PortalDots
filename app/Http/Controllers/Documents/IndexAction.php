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
        $documents = Document::visibleTo(Auth::user(), $this->selectorService->getCircle())
            ->with('versions')
            ->paginate(10);

        if ($documents->currentPage() > $documents->lastPage()) {
            return redirect($documents->url($documents->lastPage()));
        }

        return view('documents.index')
            ->with('documents', $documents);
    }
}
