<?php

namespace App\Http\Controllers\Circles\Selector;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use App\Services\Circles\SelectorService;

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
        $redirect = $request->redirect;
        if (isset($redirect) && $this->router->has($redirect)) {
            $user = Auth::user();
            $circles = $user->circles()->approved()->get();

            if (count($circles) <= 1) {
                if (count($circles) === 1) {
                    $this->selectorService->setCircle($circles[0]);
                }

                return redirect()
                    ->route($redirect);
            }

            return view('v2.circles.selector')
                ->with('redirect', $redirect)
                ->with('circles', $circles)
                ->with('error_message', session('error_message'));
        }

        return redirect()
            ->route('home');
    }
}
