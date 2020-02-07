<?php

namespace App\Http\Controllers\Auth\Password;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResetStartAction extends Controller
{
    public function __invoke()
    {
        return view('v2.auth.passwords.request');
    }
}
