<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.schedules.index');
    }
}
