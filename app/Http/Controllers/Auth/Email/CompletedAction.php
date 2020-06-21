<?php

namespace App\Http\Controllers\Auth\Email;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CompletedAction extends Controller
{
    public function __invoke()
    {
        return view('auth.verify_completed');
    }
}
