<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Http\Controllers\Controller;
use App\Eloquents\Group;

class EditAction extends Controller
{
    public function __invoke(Group $group)
    {
        $group->load('users');

        $member_ids = '';
        $members = $group->users->filter(function ($user) {
            return $user->pivot->role !== 'owner';
        });
        foreach ($members as $member) {
            $member_ids .= $member->student_id . "\r\n";
        }

        return view('staff.groups.form')
            ->with('group', $group)
            ->with('owner', $group->users->filter(function ($user) {
                return $user->pivot->role === 'owner';
            })->first())
            ->with('members', $member_ids);
    }
}
