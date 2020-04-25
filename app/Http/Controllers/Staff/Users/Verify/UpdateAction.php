<?php

namespace App\Http\Controllers\Staff\Users\Verify;

use App\Eloquents\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateAction extends Controller
{
    public function __invoke(User $user)
    {
        if ($user->hasVerifiedUnivemail()) {
            return redirect()->route('staff.users.verify', ['user' => $user])
                ->with('topAlert.title', 'すでに認証済みのユーザーです')
                ->with('topAlert.type', 'danger');
        }

        $user->is_verified_by_staff = true;
        if ($user->markUnivemailAsVerified()) {
            return redirect()->route('staff.users.verify', ['user' => $user])
                ->with('topAlert.title', '本人確認を完了しました');
        }

        return redirect()->route('staff.users.verify', ['user' => $user])
                ->with('topAlert.title', '保存に失敗しました')
                ->with('topAlert.type', 'danger');
    }
}
