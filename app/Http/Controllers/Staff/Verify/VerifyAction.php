<?php

namespace App\Http\Controllers\Staff\Verify;

use App\Http\Controllers\Controller;
use App\Services\Auth\StaffAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyAction extends Controller
{
    /**
     * @var StaffAuthService
     */
    private $staffAuthService;

    public function __construct(StaffAuthService $staffAuthService)
    {
        $this->staffAuthService = $staffAuthService;
    }

    public function __invoke(Request $request)
    {
        $result = $this->staffAuthService->verifyAndAuthenticate(Auth::user(), $request->verify_code);

        if (!$result) {
            return redirect()->route('staff.verify.index')
                ->withErrors(['verify_code' => '認証コードが間違っているか、期限切れです。再度お試しください。']);
        }

        return redirect()->route('staff.index');
    }
}
