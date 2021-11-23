<?php

namespace App\Http\Middleware;

use App\Eloquents\User;
use App\Services\Auth\StaffAuthService;
use Closure;

class RedirectIfStaffNotAuthenticated
{
    /**
     * @var StaffAuthService
     */
    private $staffAuthService;

    public function __construct(StaffAuthService $staffAuthService)
    {
        $this->staffAuthService = $staffAuthService;
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
        if (!$request->session()->get('staff_authorized') && !config('portal.enable_demo_mode')) {
            $this->staffAuthService->setPreviousUrl($request->url());
            return redirect()
                ->route('staff.verify.index');
        }

        return $next($request);
    }
}
