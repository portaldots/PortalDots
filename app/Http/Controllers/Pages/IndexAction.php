<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;
use App\Services\Circles\SelectorService;
use Illuminate\Support\Facades\Auth;

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

    public function __invoke(Request $request)
    {
        $circle = $this->selectorService->getCircle();

        $searchQuery = $request->input('query');

        if (
            !empty($searchQuery) && !Page::isMySqlFulltextIndexSupported() &&
            !Page::isMariaDbFulltextIndexSupported()
        ) {
            return redirect()
                ->route('pages.index');
        }

        $pages = Page::visibleTo(Auth::user(), $circle)
            ->byKeywords($searchQuery)
            ->with([
                'viewableTags',
                'usersWhoRead' => function ($query) {
                    $query->where('user_id', Auth::id());
                },
            ])
            ->pinned(false)
            ->paginate(10);

        if ($pages->currentPage() > $pages->lastPage()) {
            return redirect($pages->url($pages->lastPage()));
        }

        return view('pages.list')
            ->with('searchQuery', $searchQuery)
            ->with('pages', $pages);
    }
}
