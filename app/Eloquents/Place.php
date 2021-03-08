<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    protected $fillable = [
        'name',
        'type',
        'notes',
    ];

    protected $casts = [
        'type' => 'int',
    ];

    public function circles()
    {
        return $this->belongsToMany(Circle::class, 'booths')->using(Booth::class);
    }
}
