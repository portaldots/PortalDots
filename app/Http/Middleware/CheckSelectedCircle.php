<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Gate;
use Request;
use App\Services\Circles\SelectorService;

class CheckSelectedCircle
{
    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        if (
            empty($this->selectorService->getCircle()) ||
            Gate::denies('circle.belongsTo', $this->selectorService->getCircle())
        ) {
            $this->selectorService->reset();
        }

        $circles_count = Auth::user()->circles()->approved()->count();

        if (empty($this->selectorService->getCircle())) {
            if ($circles_count >= 2) {
                return redirect()
                    ->route('circles.selector.show', ['redirect_to' => Request::path()]);
            }

            $this->selectorService->setCircle(Auth::user()->circles()->approved()->first());
        }

        return $next($request);
    }
}
