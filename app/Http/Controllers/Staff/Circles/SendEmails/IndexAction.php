<?php

namespace App\Http\Controllers\Staff\Circles\SendEmails;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $circle->loadMissing('users');

        return view('staff.circles.send_emails.form')
            ->with('circle', $circle);
    }
}
