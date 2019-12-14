<?php

namespace App\Http\Controllers\Users;

use App\Eloquents\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ChangeInfoAction extends Controller
{
    public function __invoke()
    {
        $user = User::find(Auth::id());
        $belong = !empty($user->circles->all());
        return view('users.form')
            ->with('user', $user)
            ->with('belong', $belong);
    }
}
