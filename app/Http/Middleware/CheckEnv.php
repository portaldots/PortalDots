<?php

namespace App\Http\Middleware;

use Closure;
use Artisan;
use App\Services\Utils\DotenvService;

/**
 * 設定ファイルが存在しない場合、PortalDots のセットアップ方法を案内する
 */
class CheckEnv
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
        if ($this->dotenvService->getValue('APP_NOT_INSTALLED', 'false') === 'true') {
            return redirect()
                ->route('install.index');
        }
        return $next($request);
    }
}
