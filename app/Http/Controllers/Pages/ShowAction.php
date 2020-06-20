<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;
use App\Services\Circles\SelectorService;
use App\Services\Pages\ReadsService;

class ShowAction extends Controller
{
    /**
     * @var SelectorService
     */
    private $selectorService;

    /**
     * @var ReadsService
     */
    private $readsService;

    public function __construct(
        SelectorService $selectorService,
        ReadsService $readsService
    ) {
        $this->selectorService = $selectorService;
        $this->readsService = $readsService;
    }

    public function __invoke(Page $page)
    {
        $this->authorize('view', [$page, $this->selectorService->getCircle()]);

        if (Auth::check()) {
            $this->readsService->markAsRead($page, Auth::user());
        }

        return view('pages.show')
            ->with('page', $page);
    }
}
