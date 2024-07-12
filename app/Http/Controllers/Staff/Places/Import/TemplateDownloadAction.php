<?php

namespace App\Http\Controllers\Staff\Places\Import;

use App\Exports\PlacesExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TemplateDownloadAction extends Controller
{
    public function __invoke()
    {
        return Excel::download(new PlacesExport(false), "場所情報テンプレート.csv");
    }
}
