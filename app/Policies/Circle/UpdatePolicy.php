<?php

namespace App\Policies\Circle;

use App\Eloquents\Circle;
use App\Eloquents\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

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
        $participationForm = $circle->participationType->form;
        return isset($participationForm)
            && $participationForm->is_public
            && $participationForm->isOpen()
            && Gate::forUser($user)->allows('circle.belongsTo', $circle)
            && !$circle->hasSubmitted();
    }
}
