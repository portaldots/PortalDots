<?php

namespace App\Http\Controllers\Staff\Circles\SendEmails;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        return view('v2.staff.circles.mailform')
            ->with('circle', $circle);
    }
}
