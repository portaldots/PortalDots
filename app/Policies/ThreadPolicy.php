<?php

namespace App\Policies;

use App\Eloquents\Thread;
use App\Eloquents\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class ThreadPolicy
{
    use HandlesAuthorization;

    /**
     * 企画側・お問い合わせ送信者本人が会話を閲覧・投稿できるかどうか
     *
     * @param User $user
     * @param Thread $thread
     * @return bool
     */
    public function view(User $user, Thread $thread): bool
    {
        if (!empty($thread->circle_id)) {
            return Gate::forUser($user)->allows('circle.belongsTo', $thread->circle);
        }

        return (int)$thread->user_id === (int)$user->id;
    }
}
