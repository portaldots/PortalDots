<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Artisan;
use Jackiedo\DotenvEditor\DotenvEditor;

/**
 * 設定ファイルが存在しない場合、PortalDots のセットアップ方法を案内する
 */
class CheckEnv
{
    /**
     * @var DotenvEditor
     */
    private $editor;

    public function __construct(DotenvEditor $editor)
    {
        $this->editor = $editor;
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
        // テストの妨げになるため、ユニットテスト実行中は
        // このミドルウェアを適用しない
        if (App::runningUnitTests()) {
            return $next($request);
        }

        if ($this->editor->keyExists('APP_NOT_INSTALLED') && $this->editor->getValue('APP_NOT_INSTALLED') === 'true') {
            return redirect()
                ->route('install.index');
        }
        return $next($request);
    }
}
