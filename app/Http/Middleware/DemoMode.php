<?php

namespace App\Http\Middleware;

use Closure;

class DemoMode
{
    /**
     * デモモードであっても GET 以外のリクエストを許可するパス
     *
     * @var array
     */
    private $exceptPaths = [
        'login',
        'logout',
        // デモモードの場合、AddQuestionAction はダミーのレスポンスを返す
        'staff/forms/*/editor/api/add_question',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!config('portal.enable_demo_mode')) {
            return $next($request);
        }

        foreach ($this->exceptPaths as $exceptPath) {
            if ($request->is($exceptPath)) {
                return $next($request);
            }
        }

        if ($request->method() !== 'GET') {
            if ($request->ajax()) {
                return response()->json(['message' => 'デモサイトではこの機能は利用できません', 'demo_mode' => true], 403);
            } else {
                return redirect()->back()->with('topAlert.title', 'デモサイトではこの機能は利用できません');
            }
        }

        return $next($request);
    }
}
