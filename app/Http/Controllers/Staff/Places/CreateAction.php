<?php

namespace App\Http\Controllers\Staff\Places;

use App\Http\Controllers\Controller;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.places.form');
    }
}
