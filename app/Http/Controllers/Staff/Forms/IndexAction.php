<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.forms.index');
    }
}
