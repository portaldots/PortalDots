<?php

namespace App\Http\Controllers\Install\Admin;

use App\Http\Controllers\Controller;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('install.admin.form');
    }
}
