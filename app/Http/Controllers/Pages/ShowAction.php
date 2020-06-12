<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;
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

    public function __invoke(Page $page)
    {
        $this->authorize('view', [$page, $this->selectorService->getCircle()]);

        if ($page->usersWhoRead()->where('user_id', Auth::id())->doesntExist()) {
            $page->usersWhoRead()->attach(Auth::id(), ['created_at' => now()]);
        }

        return view('v2.pages.show')
            ->with('page', $page);
    }
}
