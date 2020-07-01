<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Concerns\IsNewTrait;

class Page extends Model
{
    use IsNewTrait;

    protected $fillable = [
        'title',
        'body',
        'created_by',
        'updated_by',
        'notes',
    ];

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

    public function viewableTags()
    {
        return $this->belongsToMany(Tag::class, 'page_viewable_tags')
            ->using(PageViewableTag::class);
    }

    /**
     * このお知らせを読んだユーザー
     */
    public function usersWhoRead()
    {
        return $this->belongsToMany(User::class, 'reads')->using(Read::class);
    }

    /**
     * 指定した企画が閲覧できるお知らせの一覧を取得できるクエリスコープ
     *
     * $circle を省略した場合、閲覧できる企画が限られているお知らせを
     * 除くお知らせの一覧が取得される
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Circle|null $circle
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCircle($query, ?Circle $circle = null)
    {
        $query = self::select('pages.*', 'page_viewable_tags.tag_id')
            ->leftJoin('page_viewable_tags', 'pages.id', '=', 'page_viewable_tags.page_id')
            ->whereNull('page_viewable_tags.tag_id')
            ->with('viewableTags');

        if (empty($circle)) {
            return $query;
        }

        return $query
            ->orWhereIn('page_viewable_tags.tag_id', $circle->tags->pluck('id')->all());
    }

    public function userCreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
