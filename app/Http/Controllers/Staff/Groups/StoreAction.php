<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Http\Controllers\Controller;
use App\Eloquents\Group;
use App\Http\Requests\Staff\Groups\GroupRequest;

class StoreAction extends Controller
{
    public function __invoke(GroupRequest $request)
    {
        $validated = $request->validated();

        Group::create([
            'name' => $validated['name'],
            'name_yomi' => $validated['name_yomi'],
            'notes' => $validated['notes'],
        ]);

        return redirect()
            ->route('staff.groups.create')
            ->with('topAlert.title', '団体を作成しました');
    }
}
