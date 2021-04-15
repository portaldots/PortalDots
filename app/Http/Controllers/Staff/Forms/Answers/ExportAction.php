<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\AnswersExport;
use Maatwebsite\Excel\Facades\Excel;

class ExportAction extends Controller
{
    public function __invoke(Form $form)
    {
        $now = now()->format('Y-m-d_H-i-s');
        return Excel::download(new AnswersExport($form), "form_{$form->id}_{$now}.csv");
    }
}
