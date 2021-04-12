<?php

namespace App\Http\Controllers\Staff\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Users\UserRequest;
use App\Eloquents\User;

class UpdateAction extends Controller
{
    public function __invoke(UserRequest $request, User $user)
    {
        $user->name = $request->validated()['name'];
        $user->save();

        return redirect()
            ->route('staff.users.edit', ['user' => $user])
            ->with('topAlert.title', 'ユーザーを更新しました');
    }
}
