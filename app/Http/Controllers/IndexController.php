<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function __invoke()
    {
        if (empty(Auth::user())) {
            return redirect()->route('login');
        }

        return 'ログインしています';
    }
}
