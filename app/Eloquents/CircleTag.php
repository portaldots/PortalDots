<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class CircleTag extends Pivot
{
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
