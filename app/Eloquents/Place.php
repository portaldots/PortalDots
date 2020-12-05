<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Concerns\IsNewTrait;

class Place extends Model
{
    protected $fillable = [
        'name',
        'type',
        'notes',
    ];

    public function circles()
    {
        return $this->belongsToMany(Circle::class, 'booths')->using(Booth::class);
    }
}
