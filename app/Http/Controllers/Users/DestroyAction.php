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

        $circles = $user->circles->all();

        if (!empty($circles)) {
            return redirect()
                ->route('user.delete')
                ->with('error_message', 'アカウントの削除に失敗しました。');
        }
        
        if ($user->delete()) {
            return redirect()
                ->route('home')
                ->with('success_message', 'アカウントの削除が完了しました。');
        }

        return redirect()
            ->route('user.delete')
            ->with('error_message', 'アカウントの削除に失敗しました。');
    }
}
