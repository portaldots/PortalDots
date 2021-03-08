<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Schedules\ScheduleRequest;
use App\Eloquents\Schedule;
use Carbon\Carbon;

class UpdateAction extends Controller
{
    public function __invoke(ScheduleRequest $request, Schedule $schedule)
    {
        $validated = $request->validated();

        $schedule->name = $validated['name'];
        $schedule->start_at = new Carbon($validated['start_at']);
        $schedule->place = $validated['place'];
        $schedule->description = $validated['description'];
        $schedule->notes = $validated['notes'];
        $schedule->save();

        return redirect()
            ->route('staff.schedules.edit', ['schedule' => $schedule])
            ->with('topAlert.title', '予定を更新しました');
    }
}
