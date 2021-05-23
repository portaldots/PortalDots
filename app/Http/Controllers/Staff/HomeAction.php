<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\Emails\SendEmailService;

class HomeAction extends Controller
{
    public function __invoke()
    {
        return view('staff.home')
            ->with('hasSentEmail', SendEmailService::isServiceOperational());
    }
}
