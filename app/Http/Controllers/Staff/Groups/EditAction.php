<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Http\Controllers\Controller;
use App\Eloquents\Group;

class EditAction extends Controller
{
    public function __invoke(Group $group)
    {
        return view('staff.groups.form')
            ->with('group', $group);
    }
}
