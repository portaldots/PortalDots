<?php

declare(strict_types=1);

namespace App\Auth;

use App\Eloquents\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class AppUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials): ?User
    {
        if (empty($credentials['login_id'])) {
            return null;
        }

        return (new User())->firstByLoginId($credentials['login_id']);
    }
}
