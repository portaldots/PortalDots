<?php

namespace App\Http\Controllers\Circles\Selector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Circles\SelectorService;
use App\Eloquents\Circle;

class SetAction extends Controller
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
