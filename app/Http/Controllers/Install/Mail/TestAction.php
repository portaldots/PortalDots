<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestAction extends Controller
{
    public function __invoke()
    {
        if (empty(session('install_password'))) {
            return redirect()
                ->route('install.mail.edit');
        }

        return view('v2.install.mail.test');
    }
}
