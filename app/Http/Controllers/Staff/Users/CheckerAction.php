<?php

namespace App\Http\Controllers\Staff\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckerAction extends Controller
{
    public function __invoke()
    {
        // checker.blade.php の キャンセルボタン のリンクは、
        // スタッフモードが完全に Laravel 移行後に修正
        return view('staff.users.checker');
    }
}
