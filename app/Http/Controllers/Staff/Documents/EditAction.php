<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Document;
use App\Eloquents\Schedule;

class EditAction extends Controller
{
    public function __invoke(Document $document)
    {
        return view('staff.documents.form')
            ->with('document', $document)
            ->with('schedules', Schedule::startOrder()->get());
    }
}
