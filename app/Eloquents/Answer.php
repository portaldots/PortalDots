<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property integer $id
 */
class Answer extends Model
{
    protected $fillable = [
        'form_id',
        'circle_id'
    ];

    public function details()
    {
        return $this->belongsToMany(Answer::class);
    }
}
