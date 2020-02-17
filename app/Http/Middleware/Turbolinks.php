<?php

namespace App\Http\Middleware;

use Closure;
use Session;

/**
 * リダイレクト時、Turbolinks を利用していたとしてもブラウザ上の URL 表示が
 * 適切に変化するようにするためのミドルウェア
 */
class Turbolinks
{
    private const LOCATION_SESSION_NAME = 'turbolinksLocation';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!empty($location = Session::get(self::LOCATION_SESSION_NAME))) {
            $response->headers->set('Turbolinks-Location', $location);
        }

        if (!empty($location = $response->headers->get('location'))) {
            $parsed_location = parse_url($location);
            $turbolinks_location = $parsed_location['path'] ?? '/';
            if (!empty($parsed_location['query'])) {
                $turbolinks_location .= '?' . $parsed_location['query'];
            }
            Session::flash(self::LOCATION_SESSION_NAME, $turbolinks_location);
        }

        return $response;
    }
}
