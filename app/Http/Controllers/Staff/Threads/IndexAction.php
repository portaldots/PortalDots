<?php

namespace App\Http\Controllers\Staff\Threads;

use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.threads.index');
    }
}
