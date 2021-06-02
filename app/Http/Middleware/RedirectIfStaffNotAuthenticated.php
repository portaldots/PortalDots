<?php

namespace App\Http\Middleware;

use App\Eloquents\User;
use Closure;

class RedirectIfStaffNotAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->session()->get('staff_authorized') && !config('portal.enable_demo_mode')) {
            return redirect()
                ->route('staff.verify.index');
        }

        return $next($request);
    }
}
