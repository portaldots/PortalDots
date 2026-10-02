<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class AnswerRevision extends Model
{
    protected $fillable = [
        'answer_id',
        'revision',
        'details',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'details' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
