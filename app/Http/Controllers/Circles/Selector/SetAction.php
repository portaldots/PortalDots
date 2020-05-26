<?php

namespace App\Http\Controllers\Circles\Selector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use App\Services\Circles\SelectorService;
use App\Eloquents\Circle;
use Gate;

class SetAction extends Controller
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
            $circle = Circle::approved()->findOrFail($request->circle);

            if (Gate::allows('circle.belongsTo', $circle)) {
                $this->selectorService->setCircle($circle);

                return redirect()
                    ->route($redirect);
            }
        }

        return redirect()
            ->route('home');
    }
}
