<?php

namespace App\Http\Controllers\Schedules;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Schedule;

class EndedAction extends Controller
{
    public function __invoke()
    {
        $schedules = Schedule::startOrder('desc')->ended()->get();
        $schedules = Schedule::groupByMonth($schedules);

        return view('v2.schedules.list')
            ->with('mode', 'ended')
            ->with('schedules', $schedules);
    }
}
