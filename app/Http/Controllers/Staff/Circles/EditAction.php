<?php

namespace App\Http\Controllers\Staff\Circles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Circle;

class EditAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $circle->load('users');

        $member_ids = '';
        $members = $circle->users->filter(function ($user) {
            return !$user->pivot->is_leader;
        });
        foreach ($members as $member) {
            $member_ids .= $member->student_id . "\r\n";
        }

        return view('staff.circles.form')
            ->with('circle', $circle)
            ->with('leader', $circle->users->filter(function ($user) {
                return $user->pivot->is_leader;
            })->first())
            ->with('members', $member_ids);
    }
}
