<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Http\Controllers\Controller;
use App\Eloquents\Schedule;

class DestroyAction extends Controller
{
    public function __invoke(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()
            ->route('staff.schedules.index')
            ->with('topAlert.title', '場所を削除しました');
    }
}
