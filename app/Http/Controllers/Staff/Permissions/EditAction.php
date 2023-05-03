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

        $definedPermissions = Permission::getDefinedPermissions();

        return view('staff.permissions.form')
            ->with('defined_permissions', $definedPermissions)
            ->with('user_permission_names', $user->permissions->pluck('name')->filter(function ($name) use ($definedPermissions) {
                return isset($definedPermissions[$name]);
            })->values())
            ->with('user', $user);
    }
}
