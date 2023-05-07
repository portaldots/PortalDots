<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class CircleTag extends Pivot
{
    public $incrementing = true;

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['circle'];

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
