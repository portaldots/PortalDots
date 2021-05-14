<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class CircleTag extends Pivot
{
    use LogsActivity;

    protected static $logName = 'circle_tag';

    protected static $logAttributes = [
        'id',
        'circle.id',
        'circle.name',
        'tag.id',
        'tag.name',
    ];

    protected static $logOnlyDirty = true;

    public $incrementing = true;

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
