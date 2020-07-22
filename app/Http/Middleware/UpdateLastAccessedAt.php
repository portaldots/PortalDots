<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;

class UpdateLastAccessedAt
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
        if (Auth::check()) {
            $user = $request->user();
            if (empty($user->last_accessed_at) || now()->subHour()->gte($user->last_accessed_at)) {
                $user->last_accessed_at = now();
                $user->save();
            }
        }
        return $next($request);
    }
}
