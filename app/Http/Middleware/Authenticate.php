<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            $request->session()->flash('topAlert.title', 'ログインしてください');
            $request->session()->flash('topAlert.body', 'このページにアクセスするには、まずログインしてください');
            $request->session()->flash('topAlert.keepVisible', true);
            return route('login');
        }
    }
}
