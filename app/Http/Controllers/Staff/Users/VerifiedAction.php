<?php

namespace App\Http\Controllers\Staff\Users;

use App\Eloquents\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerifiedAction extends Controller
{
    public function __invoke(User $user)
    {
        if ($user->hasVerifiedUnivemail()) {
            return redirect()->back()
                ->with('topAlert.title', 'すでに認証済みのユーザーです')
                ->with('topAlert.type', 'danger');
        }

        $user->is_verified_by_staff = true;
        if ($user->univemail === $user->email) {
            $user->email_verified_at = null;
        }
        if ($user->markUnivemailAsVerified()) {
            return redirect()->back()
                ->with('topAlert.title', '本人確認を完了しました');
        }

        return redirect()->back()
                ->with('topAlert.title', '保存に失敗しました')
                ->with('topAlert.type', 'danger');
    }
}
