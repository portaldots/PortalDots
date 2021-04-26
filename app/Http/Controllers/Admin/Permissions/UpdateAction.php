<?php

namespace App\Http\Controllers\Admin\Permissions;

use App\Eloquents\Permission;
use App\Http\Controllers\Controller;
use App\Eloquents\User;
use App\Http\Requests\Admin\Permissions\PermissionRequest;

class UpdateAction extends Controller
{
    public function __invoke(PermissionRequest $request, User $user)
    {
        $validated = $request->validated();
        $new_permissions = $validated['permissions'] ?? [];

        $exist_permissions = Permission::whereIn('name', $new_permissions)->get();
        $diff = array_udiff($new_permissions, $exist_permissions->pluck('name')->all(), 'strcasecmp');
        foreach ($diff as $insert) {
            Permission::create(['name' => $insert]);
        }

        $user->syncPermissions($new_permissions);

        return redirect()
            ->route('admin.permissions.edit', ['user' => $user])
            ->with('topAlert.title', 'スタッフの権限を更新しました');
    }
}
