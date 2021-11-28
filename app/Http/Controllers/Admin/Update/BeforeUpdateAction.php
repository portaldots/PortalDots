<?php

namespace App\Http\Controllers\Admin\Update;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BeforeUpdateAction extends Controller
{
    public function __invoke()
    {
        return view('admin.update.before-update');
    }
}
