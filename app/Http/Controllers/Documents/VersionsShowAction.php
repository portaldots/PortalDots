<?php

namespace App\Http\Controllers\Documents;

use Storage;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;
use App\Eloquents\DocumentVersion;
use App\Services\Circles\SelectorService;

class VersionsShowAction extends Controller
{
    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    public function __invoke(Document $document, DocumentVersion $version)
    {
        $isVisible = Document::whereKey($document->id)
            ->visibleTo(Auth::user(), $this->selectorService->getCircle())
            ->exists();

        if (!$isVisible) {
            abort(404);
            return;
        }

        return response()->file(Storage::path($version->path));
    }
}
