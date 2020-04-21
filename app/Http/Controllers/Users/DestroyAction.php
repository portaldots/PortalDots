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
        $user = User::find(Auth::id());

        $circles = $user->circles()->all();

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
