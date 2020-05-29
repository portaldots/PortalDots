<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DeleteAction extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();
        $circles = $user->circles()->get();

        return view('v2.users.delete')
            ->with('is_admin', $user->is_admin)
            ->with('is_staff', $user->is_staff)
            ->with('belong', !$circles->isEmpty());
    }
}
