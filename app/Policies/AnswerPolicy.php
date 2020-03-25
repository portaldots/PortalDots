<?php

namespace App\Policies;

use App\Eloquents\User;
use App\Eloquents\Answer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class AnswerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the answer.
     *
     * @param  \App\Eloquents\User  $user
     * @param  \App\Answer  $answer
     * @return mixed
     */
    public function view(User $user, Answer $answer)
    {
        return Gate::forUser($user)->allows('circle.belongsTo', $answer->circle);
    }

    /**
     * Determine whether the user can update the answer.
     *
     * @param  \App\Eloquents\User  $user
     * @param  \App\Answer  $answer
     * @return mixed
     */
    public function update(User $user, Answer $answer)
    {
        return Gate::forUser($user)->allows('circle.belongsTo', $answer->circle);
    }

    /**
     * Determine whether the user can delete the answer.
     *
     * @param  \App\Eloquents\User  $user
     * @param  \App\Answer  $answer
     * @return mixed
     */
    public function delete(User $user, Answer $answer)
    {
        return Gate::forUser($user)->allows('circle.belongsTo', $answer->circle);
    }
}
