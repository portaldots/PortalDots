<?php

namespace App\Http\Controllers\Staff\Groups\SendEmails;

use App\Eloquents\Group;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke(Group $group)
    {
        $group->loadMissing('users');

        return view('staff.groups.send_emails.form')
            ->with('group', $group);
    }
}
