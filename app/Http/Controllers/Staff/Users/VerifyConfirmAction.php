<?php

namespace App\Http\Controllers\Staff\Users;

use App\Eloquents\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// TODO: スタッフモードのユーザー情報管理ページが Project v2 化したら、このファイルは消す
class VerifyConfirmAction extends Controller
{
    public function __invoke(User $user)
    {
        return view('staff.users.verify.index')
            ->with('user', $user);
    }
}
