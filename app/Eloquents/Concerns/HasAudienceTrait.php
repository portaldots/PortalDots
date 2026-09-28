<?php

declare(strict_types=1);

namespace App\Eloquents\Concerns;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Circle;
use App\Eloquents\User;

/**
 * is_public (公開設定) とは別に、誰が閲覧できるかを表す audience カラムを持つ
 * モデルに共通の公開範囲チェックを提供する。利用するモデルは viewableTags() と
 * viewableCircles() の belongsToMany リレーション、および scopePublic() を
 * 持っている必要がある。
 */
trait HasAudienceTrait
{
    /**
     * 公開されており、かつ指定したユーザー・企画が閲覧可能なものに限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param User|null $user
     * @param Circle|null $circle
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleTo($query, ?User $user, ?Circle $circle = null)
    {
        return $query->public()->where(function ($query) use ($user, $circle) {
            $query->where('audience', AudiencePolicy::EVERYONE);

            if (!empty($user)) {
                $query->orWhere('audience', AudiencePolicy::SIGNED_IN);
            }

            if (!empty($circle)) {
                $tagIds = $circle->tags->pluck('id')->all();
                $query->orWhere(function ($query) use ($circle, $tagIds) {
                    $query->where('audience', AudiencePolicy::SELECTED)
                        ->where(function ($query) use ($circle, $tagIds) {
                            $query->whereHas('viewableTags', function ($query) use ($tagIds) {
                                $query->whereIn('tags.id', $tagIds);
                            })->orWhereHas('viewableCircles', function ($query) use ($circle) {
                                $query->where('circles.id', $circle->id);
                            });
                        });
                });
            }
        });
    }
}
