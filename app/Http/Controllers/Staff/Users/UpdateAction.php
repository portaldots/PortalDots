<?php

namespace App\Http\Controllers\Staff\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Users\UserRequest;
use App\Eloquents\User;

class UpdateAction extends Controller
{
    public function __invoke(UserRequest $request, User $user)
    {
        $validated = $request->validated();
        $user->student_id = $validated['student_id'];
        $user->name = $validated['name'];
        $user->name_yomi = $validated['name_yomi'];
        $user->email = $validated['email'];
        $user->tel = $validated['tel'];
        $user->save();

        return redirect()
            ->route('staff.users.edit', ['user' => $user])
            ->with('topAlert.title', 'ユーザーを更新しました');
    }
}
