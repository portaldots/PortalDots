<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.groups.index');
    }
}
