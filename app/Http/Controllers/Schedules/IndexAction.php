<?php

namespace App\Http\Controllers\Schedules;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Schedule;

class IndexAction extends Controller
{
    public function __invoke()
    {
        $schedules = Schedule::startOrder('asc')->notStarted()->get();
        $schedules = Schedule::groupByMonth($schedules);

        return view('v2.schedules.list')
            ->with('mode', 'notStarted')
            ->with('schedules', $schedules);
    }
}
