<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AnswerDetail extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('answer_detail')
            ->logOnly([
                'id',
                'answer.id',
                'question.id',
                'question.form_id',
                'question.name',
            ])
            ->logOnlyDirty();
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
