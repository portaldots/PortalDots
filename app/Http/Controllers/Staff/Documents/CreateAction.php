<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.documents.form');
    }
}
