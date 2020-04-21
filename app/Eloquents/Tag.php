<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name'
    ];

    public function circles()
    {
        $this->belongsToMany(Circle::class);
    }
}
