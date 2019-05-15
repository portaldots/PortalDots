<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
