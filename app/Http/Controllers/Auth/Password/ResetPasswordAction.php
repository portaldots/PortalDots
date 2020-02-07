<?php

namespace App\Http\Controllers\Auth\Password;

use App\Eloquents\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ResetPasswordAction extends Controller
{
    public function __invoke(User $user)
    {
        return view('v2.auth.passwords.reset');
    }
}
