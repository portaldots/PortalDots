<?php

namespace App\Http\Controllers\Staff\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Request $request)
    {
        return view('staff.users.index');
    }
}
