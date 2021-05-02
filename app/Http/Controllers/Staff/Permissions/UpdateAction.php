<?php

namespace App\Http\Controllers\Staff\Permissions;

use App\Eloquents\Permission;
use App\Http\Controllers\Controller;
use App\Eloquents\User;
use App\Http\Requests\Admin\Permissions\PermissionRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAction extends Controller
{
    public function __invoke(PermissionRequest $request, User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()
                ->withInput()
                ->withErrors(['permissions' => '自分自身の権限設定は変更できません']);
        }

        $validated = $request->validated();
        $new_permissions = $validated['permissions'] ?? [];

        $exist_permissions = Permission::whereIn('name', $new_permissions)->get();
        $diff = array_udiff($new_permissions, $exist_permissions->pluck('name')->all(), 'strcasecmp');
        foreach ($diff as $insert) {
            Permission::create(['name' => $insert]);
        }

        $user->syncPermissions($new_permissions);

        return redirect()
            ->route('staff.permissions.edit', ['user' => $user])
            ->with('topAlert.title', 'スタッフの権限を更新しました');
    }
}
