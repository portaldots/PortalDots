<?php

namespace App\Http\Controllers\Staff\Permissions;

use App\Eloquents\Permission;
use App\Http\Controllers\Controller;
use App\Eloquents\User;

class EditAction extends Controller
{
    public function __invoke(User $user)
    {
        $user->load('permissions');

        return view('staff.permissions.form')
            ->with('defined_permissions', Permission::getDefinedPermissions())
            ->with('user', $user);
    }
}
