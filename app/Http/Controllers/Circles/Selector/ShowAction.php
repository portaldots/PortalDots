<?php

namespace App\Http\Controllers\Circles\Selector;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use App\Services\Circles\SelectorService;
use App\Eloquents\CustomForm;

class ShowAction extends Controller
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(Router $router, SelectorService $selectorService)
    {
        $this->router = $router;
        $this->selectorService = $selectorService;
    }

    public function __invoke(Request $request)
    {
        $redirect_to = $request->redirect_to;
        if (isset($redirect_to)) {
            $user = Auth::user();
            $circles = $this->selectorService->getSelectableCirclesList($user);
            $not_submitted_circles = $user->circles()->notSubmitted()->get();

            return view('circles.selector')
                ->with('redirect_to', $redirect_to)
                ->with('circles', $circles)
                ->with('not_submitted_circles', $not_submitted_circles)
                ->with('error_message', session('error_message'));
        }

        abort(404);
    }
}
