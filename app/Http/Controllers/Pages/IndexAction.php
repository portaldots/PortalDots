<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;
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

        $pages = Page::byCircle($circle)->with(['usersWhoRead' => function ($query) {
            $query->where('user_id', Auth::id());
        }])->paginate(10);

        if ($pages->currentPage() > $pages->lastPage()) {
            return redirect($pages->url($pages->lastPage()));
        }

        return view('pages.index')
            ->with('pages', $pages);
    }
}
