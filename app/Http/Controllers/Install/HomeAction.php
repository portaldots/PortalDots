<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeAction extends Controller
{
    public function __invoke()
    {
        return view('v2.install.index');
    }
}
