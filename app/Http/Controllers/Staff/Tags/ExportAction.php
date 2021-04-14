<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Exports\TagsExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke()
    {
        $now = now()->format('Y-m-d_H-i-s');
        return Excel::download(new TagsExport(), "tags_{$now}.csv");
    }
}
