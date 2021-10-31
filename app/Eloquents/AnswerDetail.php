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

    protected $appends = ['user_friendly_file_name'];

    public function answerOf()
    {
        return $this->belongsTo(Answer::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function getUserFriendlyFileNameAttribute(): ?string
    {
        if (empty($this->question) || empty($this->answerOf) || empty($this->answerOf->circle)) {
            dd($this->question, $this->answer, $this->answerOf);
            return null;
        }

        $question_id = str_pad($this->question->id, 6, "0", STR_PAD_LEFT);
        $answer_id = str_pad($this->answerOf->id, 6, "0", STR_PAD_LEFT);
        $circle_id = str_pad($this->answerOf->circle->id, 6, "0", STR_PAD_LEFT);
        $circle_name = $this->answerOf->circle->name;
        return implode('_', [
            'q' . $question_id,
            'a' . $answer_id,
            'c' . $circle_id,
            $circle_name,
            $this->id
        ]);
    }
}
