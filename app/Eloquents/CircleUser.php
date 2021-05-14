<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CircleUser extends Pivot
{
    public $incrementing = true;

    protected $casts = [
        'is_leader' => 'bool',
    ];

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
