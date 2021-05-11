<?php

namespace App\Http\Controllers\Admin\ActivityLog;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class IndexAction extends Controller
{
    public function __invoke()
    {
        $activity_log = Activity::orderBy('updated_at', 'desc')->paginate(20);

        if ($activity_log->currentPage() > $activity_log->lastPage()) {
            return redirect($activity_log->url($activity_log->lastPage()));
        }

        return view('admin.activity_log.list')
            ->with('activity_log', $activity_log);
    }
}
