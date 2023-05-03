<?php

namespace App\Policies\Circle;

use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Eloquents\CustomForm;
use App\Eloquents\ParticipationType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class CreatePolicy
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

    public function __invoke(?User $user, ?ParticipationType $participationType = null)
    {
        if (empty($participationType)) {
            return ParticipationType::public()->open()->count() > 0;
        }

        return $participationType->form->is_public
            && $participationType->form->isOpen();
    }
}
