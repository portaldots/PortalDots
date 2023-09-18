<?php

namespace App\Http\Controllers\Staff\Places\Import;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShowAction extends Controller
{

    public function __invoke()
    {
        return view('staff.places.import');
    }
}
