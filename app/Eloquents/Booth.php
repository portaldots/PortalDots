<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Booth extends Pivot
{
    public $incrementing = true;
    public $timestamps = false;

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }
}
