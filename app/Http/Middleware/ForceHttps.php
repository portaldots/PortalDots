<?php

namespace App\Http\Middleware;

use \Illuminate\Http\Request;
use Closure;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && config('app.force_https')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
