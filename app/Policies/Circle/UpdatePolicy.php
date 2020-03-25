<?php

namespace App\Policies\Circle;

use App\Eloquents\Circle;
use App\Eloquents\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class UpdatePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function __invoke(User $user, Circle $circle)
    {
        return Gate::forUser($user)->allows('circle.belongsTo', $circle)
            && !$circle->hasSubmitted();
    }
}
