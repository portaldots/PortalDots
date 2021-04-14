<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Exports\SchedulesExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke()
    {
        $now = now()->format('Y-m-d_H-i-s');
        return Excel::download(new SchedulesExport(), "schedules_{$now}.csv");
    }
}
