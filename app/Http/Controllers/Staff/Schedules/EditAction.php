<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Http\Controllers\Controller;
use App\Eloquents\Schedule;

class EditAction extends Controller
{
    public function __invoke(Schedule $schedule)
    {
        return view('staff.schedules.form')
            ->with('schedule', $schedule);
    }
}
