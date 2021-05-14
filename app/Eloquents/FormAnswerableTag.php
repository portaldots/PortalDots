<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class FormAnswerableTag extends Pivot
{
    use LogsActivity;

    protected static $logName = 'form_answerable_tag';

    protected static $logAttributes = [
        'id',
        'form.id',
        'form.name',
        'tag.id',
        'tag.name',
    ];

    protected static $logOnlyDirty = true;

    public $incrementing = true;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
