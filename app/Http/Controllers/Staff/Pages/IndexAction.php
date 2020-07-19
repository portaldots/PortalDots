<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Request $request)
    {
        return view('staff.pages.index');
    }
}
