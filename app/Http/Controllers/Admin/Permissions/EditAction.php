<?php

namespace App\Http\Controllers\Admin\Permissions;

use App\Eloquents\Permission;
use App\Http\Controllers\Controller;
use App\Eloquents\User;

class EditAction extends Controller
{
    public function __invoke(User $user)
    {
        $user->load('permissions');

        return view('admin.permissions.form')
            ->with('defined_permissions', Permission::getDefinedPermissions())
            ->with('user', $user);
    }
}
