<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRealAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('portal.enable_demo_mode') || !$request->user()?->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
