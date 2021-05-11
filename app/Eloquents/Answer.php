<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property integer $id
 */
class Answer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'form_id',
        'circle_id'
    ];

    // WIP: many-to-many のリレーションはログに残せる？
    protected static $logAttributes = ['name', 'text', 'user.name'];

    public function details()
    {
        return $this->hasMany(AnswerDetail::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
