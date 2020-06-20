<?php

namespace App\Http\Middleware;

use Closure;
use Artisan;
use App\Services\Utils\DotenvService;

/**
 * PortalDots がインストール済の場合、トップページへリダイレクトする
 */
class DenyIfInstalled
{
    /**
     * @var DotenvService
     */
    private $dotenvService;

    public function __construct(DotenvService $dotenvService)
    {
        $this->dotenvService = $dotenvService;
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
        if ($this->dotenvService->getValue('APP_NOT_INSTALLED', 'false') !== 'true') {
            abort(404);
        }
        return $next($request);
    }
}
