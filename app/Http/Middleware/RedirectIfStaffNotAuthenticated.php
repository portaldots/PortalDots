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
        if (! $request->session()->get('staff_authorized')) {
            // CodeIgntier 側の二段階認証画面へリダイレクト
            return redirect('/home_staff/verify_access');
        }

        return $next($request);
    }
}
