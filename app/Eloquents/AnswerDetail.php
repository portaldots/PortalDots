<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class AnswerDetail extends Model
{
    use LogsActivity;

    protected static $logName = 'answer_detail';

    protected static $logAttributes = [
        'id',
        'answer.id',
        'question.id',
        'question.form_id',
        'question.name',
    ];

    protected static $logOnlyDirty = true;

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
