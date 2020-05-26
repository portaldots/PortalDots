<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Concerns\IsNewTrait;

class Page extends Model
{
    use IsNewTrait;

    /**
     * モデルの「初期起動」メソッド
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('updated_at', function (Builder $builder) {
            $builder->orderBy('updated_at', 'desc');
        });
    }

    /**
     * 指定した企画が閲覧できるお知らせの一覧を取得できるクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Circle|null $circle
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCircle($query, ?Circle $circle)
    {
        $query = self::select('pages.*', 'page_viewable_tags.tag_id')
            ->leftJoin('page_viewable_tags', 'pages.id', '=', 'page_viewable_tags.page_id')
            ->whereNull('page_viewable_tags.tag_id');

        if (empty($circle)) {
            return $query;
        }

        return $query
            ->orWhereIn('page_viewable_tags.tag_id', $circle->tags->pluck('id')->all());
    }
}
