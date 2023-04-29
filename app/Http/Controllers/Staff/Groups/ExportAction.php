<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Exports\GroupsExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke()
    {
        $now = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download(new GroupsExport(), "団体一覧_{$now}.csv");
    }
}
