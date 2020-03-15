<?php

namespace App\Http\Controllers\Staff\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckerAction extends Controller
{
    public function __invoke()
    {
        return view('staff.users.checker');
    }
}
