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
        $user->univemail_local_part = $validated['univemail_local_part'];
        $user->univemail_domain_part = $validated['univemail_domain_part'];
        $user->name = $validated['name'];
        $user->name_yomi = $validated['name_yomi'];
        $user->email = $validated['email'];
        $user->tel = $validated['tel'];
        $user->notes = $validated['notes'];

        if (!empty($validated['user_type'])) {
            $user->is_staff = in_array($validated['user_type'], ['staff', 'admin'], true);
            $user->is_admin = $validated['user_type'] === 'admin';
        }

        $user->save();

        return redirect()
            ->back()
            ->with('topAlert.title', 'ユーザーを更新しました');
    }
}
