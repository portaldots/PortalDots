<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Http\Controllers\Controller;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.schedules.form');
    }
}
