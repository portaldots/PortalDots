<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Http\Controllers\Controller;

class AllAction extends Controller
{
    public function __invoke()
    {
        return view('staff.circles.data_grid');
    }
}
