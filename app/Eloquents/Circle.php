<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    protected $fillable = [
        'name',
        'created_by',
        'updated_by',
        'notes',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->using(CircleUser::class)->withPivot('is_leader');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
