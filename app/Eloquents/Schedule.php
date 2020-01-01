<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $dates = [
        'start_at',
    ];

    /**
     * 取得順を開催順にするクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartOrder($query)
    {
        return $query->orderBy('start_at', 'asc');
    }

    /**
     * 未開催の予定に限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotStarted($query)
    {
        return $query->where('start_at', '>', now());
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
