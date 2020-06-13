<?php

namespace App\Policies;

use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Circle;
use Illuminate\Auth\Access\HandlesAuthorization;

class PagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the page.
     *
     * @param  User|null  $user
     * @param  Page  $page
     * @param  Circle|null  $circle
     * @return bool
     */
    public function view(?User $user, Page $page, ?Circle $circle): bool
    {
        if (!$page->viewableTags->isEmpty()) {
            if (empty($circle)) {
                return false;
            }
            return $circle->tags()->whereIn('tags.id', $page->viewableTags->pluck('id')->all())->exists();
        }
        return true;
    }
}
