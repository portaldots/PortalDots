<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Request $request)
    {
        return view('staff.tags.index');
    }
}
