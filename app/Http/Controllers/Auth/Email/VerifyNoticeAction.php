<?php

namespace App\Http\Controllers\Auth\Email;

use App\Eloquents\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class VerifyNoticeAction extends Controller
{
    public function __invoke()
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user->areBothEmailsVerified()) {
            return redirect()->route('home');
        }

        return view('auth.verify');
    }
}
