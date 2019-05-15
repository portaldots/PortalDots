<?php

namespace App\Http\Controllers\Auth\Email;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VerifyNoticeAction extends Controller
{
    public function __invoke()
    {
        return view('auth.verify');
    }
}
