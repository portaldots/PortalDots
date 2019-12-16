<?php

namespace App\Http\Controllers\Staff\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\User;

class PostCheckerAction extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(Request $request)
    {
        $user = $this->user->firstByStudentId($request->student_id);

        // checker.blade.php の キャンセルボタン のリンクは Laravel以降後に修正
        return view('staff.users.checker')
            ->with('user', $user);
    }
}
