<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Exports\FormsExport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke()
    {
        $now = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download(new FormsExport(), "申請一覧_{$now}.csv");
    }
}
