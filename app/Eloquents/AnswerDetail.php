<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class AnswerDetail extends Model
{
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
