<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipationType extends Model
{
    use HasFactory;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function circles()
    {
        return $this->hasMany(Circle::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
