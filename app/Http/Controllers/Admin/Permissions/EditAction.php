<?php

namespace App\Http\Controllers\Admin\Permissions;

use App\Http\Controllers\Controller;
use App\Eloquents\User;

class EditAction extends Controller
{
    public function __invoke(User $user)
    {
        return view('staff.users.form')
            ->with('user', $user);
    }
}
