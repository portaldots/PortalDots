<?php

namespace App\Http\Controllers\Staff\Circles\SendEmails;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        if ($circle->users()->get()->isEmpty()) {
            return redirect("home_staff/circles/read/{$circle->id}");
        }
        return view('v2.staff.circles.send_emails.form')
            ->with('circle', $circle);
    }
}
