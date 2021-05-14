<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class Booth extends Pivot
{
    use LogsActivity;

    protected static $logName = 'booth';

    protected static $logAttributes = [
        'id',
        'place.id',
        'place.name',
        'circle.id',
        'circle.name',
    ];

    protected static $logOnlyDirty = true;

    public $incrementing = true;
    public $timestamps = false;

    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }
}
