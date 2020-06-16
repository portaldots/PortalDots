<?php

namespace App\Policies\Circle;

use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Eloquents\CustomForm;
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
        $custom_form = CustomForm::getFormByType('circle');
        return isset($custom_form)
            && $custom_form->is_public
            && $custom_form->isOpen()
            && Gate::forUser($user)->allows('circle.belongsTo', $circle)
            && !$circle->hasSubmitted();
    }
}
