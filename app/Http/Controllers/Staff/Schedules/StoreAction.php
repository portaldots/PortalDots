<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Schedules\ScheduleRequest;
use App\Eloquents\Schedule;
use Carbon\Carbon;

class StoreAction extends Controller
{
    public function __invoke(ScheduleRequest $request)
    {
        $validated = $request->validated();

        Schedule::create([
            'name' => $validated['name'],
            'start_at' => new Carbon($validated['start_at']),
            'place' => $validated['place'],
            'description' => $validated['description'],
            'notes' => $validated['notes'],
        ]);

        return redirect()
            ->route('staff.schedules.create')
            ->with('topAlert.title', '予定を作成しました');
    }
}
