<?php

declare(strict_types=1);

namespace App\Services\Users;

use App\Eloquents\User;
use App\Notifications\Users\PasswordChangedNotification;
use Illuminate\Support\Facades\Hash;

class ChangePasswordService
{
    public function changePassword(User $user, string $new_password)
    {
        $user->password = Hash::make($new_password);
        $user->save();

        $user->notify(new PasswordChangedNotification($user));
    }
}
