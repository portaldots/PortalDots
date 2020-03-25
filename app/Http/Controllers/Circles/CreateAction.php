<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('v2.circles.form');
    }
}
