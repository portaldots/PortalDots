<?php

namespace App\Http\Controllers\Staff\Places;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\PlacesExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke()
    {
        $now = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download(new PlacesExport(), "places_{$now}.csv");
    }
}
