<?php

namespace App\Http\Controllers\Staff\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\User;

class CheckerListAction extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(Request $request)
    {
        if (empty($request->student_id)) {
            return [];
        }

        $users = $this->user->byStudentIdStartWith($request->student_id)
            ->select(
                'id',
                'student_id',
                'name_family',
                'name_family_yomi',
                'name_given',
                'name_given_yomi',
                'email_verified_at',
                'univemail_verified_at',
                'created_at',
                'updated_at'
            )
            ->get();

        return $users;
    }
}
