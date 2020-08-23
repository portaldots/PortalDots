<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Schedule;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.forms.form');
    }
}
