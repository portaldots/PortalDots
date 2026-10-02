<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\HomeLanding;
use App\Eloquents\Circle;
use App\Eloquents\User;

class DefaultHomeLanding implements HomeLanding
{
    public function redirectFor(?User $user, ?Circle $circle): ?string
    {
        return null;
    }
}
