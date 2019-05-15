<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
