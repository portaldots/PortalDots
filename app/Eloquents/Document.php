<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $casts = [
        'is_public' => 'bool',
        'is_important' => 'bool',
    ];

    /**
     * 公開されている配布資料に限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
