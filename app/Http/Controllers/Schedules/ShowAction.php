<?php

namespace App\Http\Controllers\Schedules;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Schedule;

class ShowAction extends Controller
{
    public function __invoke(Schedule $schedule)
    {
        $schedule->load('documents');

        return view('v2.schedules.show')
            ->with('schedule', $schedule);
    }
}
