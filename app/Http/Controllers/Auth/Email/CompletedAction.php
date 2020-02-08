<?php

namespace App\Http\Controllers\Auth\Email;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompletedAction extends Controller
{
    public function __invoke()
    {
        return view('v2.auth.verify_completed');
    }
}
