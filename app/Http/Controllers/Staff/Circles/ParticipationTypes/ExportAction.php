<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Exports\CirclesExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke(ParticipationType $participationType)
    {
        $now = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download(
            new CirclesExport($participationType),
            "企画一覧_{$participationType->name}_{$now}.csv"
        );
    }
}
