<?php

namespace App\Http\Middleware;

use Closure;
use App;

class Localization
{
    private const LOCALE_COOKIE_NAME = 'locale';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = 'ja';
        if (!empty($request->locale)) {
            // ?locale=en などがついていれば、それを採用
            $locale = $request->locale;
        } elseif (!empty($request->cookie(self::LOCALE_COOKIE_NAME))) {
            // 次に Cookie から取得
            $locale = $request->cookie(self::LOCALE_COOKIE_NAME);
        } else {
            // ブラウザの言語設定を採用
            $accept_language = $request->server('HTTP_ACCEPT_LANGUAGE');
            $locale = substr(explode(',', $accept_language)[0], 0, 2);
        }
        App::setLocale($locale);
        $response = $next($request);
        $response->cookie(
            self::LOCALE_COOKIE_NAME,
            $locale,
            60 * 24 * 30, // 30 days
            '/'
        );
        return $response;
    }
}
