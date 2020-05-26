<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
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
        if (
            Auth::check() && Auth::user()->circles()->approved()->count() > 0
            && empty($this->selectorService->getCircle())
        ) {
            return redirect()
                ->route('circles.selector.show', ['redirect' => $request->route()->getName()]);
        }

        return $next($request);
    }
}
