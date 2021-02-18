<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    public function circles()
    {
        return $this->belongsToMany(Circle::class, 'booths')->using(Booth::class);
    }
}
