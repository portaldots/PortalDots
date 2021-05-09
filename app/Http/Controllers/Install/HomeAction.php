<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;

class HomeAction extends Controller
{
    public function __invoke()
    {
        return view('install.index');
    }
}
