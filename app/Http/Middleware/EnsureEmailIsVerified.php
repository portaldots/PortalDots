<?php

namespace App\Http\Middleware;

use App\Eloquents\User;
use Closure;

class EnsureEmailIsVerified
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
        /** @var User $user */
        $user = $request->user();

        if (! $user || ! $user->areBothEmailsVerified()) {
            return redirect()
                ->route('verification.notice')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', 'このページにアクセスするには、メール認証を完了してください');
        }

        $user->setSignedUp();

        return $next($request);
    }
}
