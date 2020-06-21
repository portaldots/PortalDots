<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChangePasswordAction extends Controller
{
    public function __invoke()
    {
        return view('users.change_password');
    }
}
