<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\Controller;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.tags.form');
    }
}
