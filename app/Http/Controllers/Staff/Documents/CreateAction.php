<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Schedule;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.documents.form')
            ->with('schedules', Schedule::startOrder()->get());
    }
}
