<?php

namespace App\Http\Controllers\Users;

use App\Eloquents\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DestroyAction extends Controller
{
    public function __invoke()
    {
        $user = Auth::user();

        $circles = $user->circles;

        if ($user->is_admin) {
            return redirect()
                ->route('user.delete')
                ->with('topAlert.title', '管理者ユーザーの削除はできません。');
        }

        if (!$circles->isEmpty()) {
            return redirect()
                ->route('user.delete')
                ->with('topAlert.title', '企画に所属しているため、アカウント削除はできません。');
        }

        if ($user->delete()) {
            return redirect()
                ->route('home')
                ->with('topAlert.title', 'アカウントの削除が完了しました。');
        }

        return redirect()
            ->route('user.delete')
            ->with('topAlert.title', 'アカウントの削除に失敗しました。');
    }
}
