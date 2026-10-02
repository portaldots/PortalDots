<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Eloquents\Circle;
use App\Eloquents\User;

interface HomeLanding
{
    public function redirectFor(?User $user, ?Circle $circle): ?string;
}
