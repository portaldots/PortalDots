<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'users_count_min',
        'users_count_max',
        'form_id',
    ];

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
