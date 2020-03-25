<?php

namespace App\Http\Controllers\Users;

use App\Eloquents\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EditInfoAction extends Controller
{
    public function __invoke()
    {
        $user = User::find(Auth::id());
        return view('v2.users.edit')
            ->with('user', $user)
            ->with('circles', $user->circles()->withoutGlobalScope('approved')->get());
    }
}
