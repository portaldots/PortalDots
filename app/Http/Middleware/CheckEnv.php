<?php

namespace App\Http\Middleware;

use Closure;

/**
 * 設定ファイルが存在しない場合、PortalDots のセットアップ方法を案内する
 */
class CheckEnv
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
        if (empty(config('app.key'))) {
            return response(view('errors.no_config_error'));
        }
        return $next($request);
    }
}
