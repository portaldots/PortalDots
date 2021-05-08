<?php

namespace App\Http\Controllers\Staff\SendEmails;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Emails\SendEmailService;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.send_emails.index')
            ->with('hasSentEmail', SendEmailService::isServiceOperational());
    }
}
