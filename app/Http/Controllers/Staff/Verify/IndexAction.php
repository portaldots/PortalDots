<?php

namespace App\Http\Controllers\Staff\Verify;

use App\Http\Controllers\Controller;
use App\Services\Auth\StaffAuthService;
use Illuminate\Support\Facades\Auth;

class IndexAction extends Controller
{
    /**
     * @var StaffAuthService
     */
    private $staffAuthService;

    public function __construct(StaffAuthService $staffAuthService)
    {
        $this->staffAuthService = $staffAuthService;
    }

    public function __invoke()
    {
        $this->staffAuthService->send(Auth::user());
        return view('staff.verify.index');
    }
}
