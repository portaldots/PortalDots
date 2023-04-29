<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Http\Controllers\Controller;
use App\Eloquents\Group;
use App\Http\Requests\Staff\Groups\GroupRequest;

class UpdateAction extends Controller
{
    public function __invoke(GroupRequest $request, Group $group)
    {
        $validated = $request->validated();

        $group->update([
            'name' => $validated['name'],
            'name_yomi' => $validated['name_yomi'],
            'notes' => $validated['notes'],
        ]);

        return redirect()
            ->back()
            ->with('topAlert.title', '団体を更新しました');
    }
}
