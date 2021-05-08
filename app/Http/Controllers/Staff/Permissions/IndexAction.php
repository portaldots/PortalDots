<?php

namespace App\Http\Controllers\Staff\Permissions;

use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.permissions.index');
    }
}
