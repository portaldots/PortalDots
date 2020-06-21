<?php

namespace App\Http\Controllers\Install\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateAction extends Controller
{
    public function __invoke(Request $request)
    {
        return view('install.admin.form');
    }
}
