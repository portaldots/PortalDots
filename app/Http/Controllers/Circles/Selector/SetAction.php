<?php

namespace App\Http\Controllers\Circles\Selector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use App\Services\Circles\SelectorService;
use App\Eloquents\Circle;
use Gate;

class SetAction extends Controller
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(Router $router, SelectorService $selectorService)
    {
        $this->router = $router;
        $this->selectorService = $selectorService;
    }

    public function __invoke(Request $request)
    {
        $redirect_to = $request->redirect_to;
        if (isset($redirect_to)) {
            $url = $this->getSanitizedUrl($redirect_to);
            $circle = Circle::approved()->findOrFail($request->circle);

            if (Gate::allows('circle.belongsTo', $circle)) {
                $this->selectorService->setCircle($circle);

                return redirect($url);
            }
        }

        abort(404);
    }

    private function getSanitizedUrl($url)
    {
        // $urlが「//evil.example.com/evil」のような文字列になっている場合、
        // PortalDots 外のページへリダイレクトしてしまう(オープンリダイレクト脆弱性)
        // のため、先頭にスラッシュがついている場合は取り除く。
        //
        // 先頭のスラッシュを取り除いた上で、スラッシュを1つだけ先頭に追加する。
        return '/' . str_replace("\n", '', preg_replace('/^\/+/', '', $url));
    }
}
