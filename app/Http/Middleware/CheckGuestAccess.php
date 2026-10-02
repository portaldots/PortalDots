<?php

namespace App\Http\Middleware;

use App\Contracts\GuestAccessPolicy;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckGuestAccess
{
    /**
     * @var GuestAccessPolicy
     */
    private $guestAccessPolicy;

    public function __construct(GuestAccessPolicy $guestAccessPolicy)
    {
        $this->guestAccessPolicy = $guestAccessPolicy;
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
        if (Auth::check() || $this->guestAccessPolicy->allowsGuests()) {
            return $next($request);
        }

        $request->session()->flash('topAlert.title', 'ログインしてください');
        $request->session()->flash('topAlert.body', 'このページにアクセスするには、まずログインしてください');
        $request->session()->flash('topAlert.keepVisible', true);

        return redirect()->guest(route('login'));
    }
}
