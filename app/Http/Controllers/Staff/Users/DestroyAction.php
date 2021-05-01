<?php

namespace App\Http\Controllers\Staff\Users;

use App\Http\Controllers\Controller;
use App\Eloquents\User;

class DestroyAction extends Controller
{
    public function __invoke(User $user)
    {
        $user->delete();
        return redirect()
                ->route('staff.users.index')
                ->with('topAlert.title', 'ユーザーを削除しました');
    }
}
