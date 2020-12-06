<?php

namespace App\Http\Controllers\Staff\Places;

use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.places.index');
    }
}
