<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\AnswersExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke(Form $form)
    {
        $now = Carbon::now()->format('Y-m-d_H-i-s');
        return Excel::download(new AnswersExport($form), "{$form->name}_回答一覧_{$now}.csv");
    }
}
