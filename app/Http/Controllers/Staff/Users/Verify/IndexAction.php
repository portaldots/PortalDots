<?php

namespace App\Http\Controllers\Staff\Users\Verify;

use App\Eloquents\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(User $user)
    {
        return view('staff.users.verify.index')
            ->with('user', $user);
    }
}
