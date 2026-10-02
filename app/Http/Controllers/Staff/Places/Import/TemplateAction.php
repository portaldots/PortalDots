<?php

namespace App\Http\Controllers\Staff\Places\Import;

use App\Exports\PlacesImportTemplateExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class TemplateAction extends Controller
{
    public function __invoke()
    {
        return Excel::download(
            new PlacesImportTemplateExport(),
            '場所情報テンプレート.csv',
            ExcelFormat::CSV
        );
    }
}
