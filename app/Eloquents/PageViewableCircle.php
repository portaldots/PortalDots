<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PageViewableCircle extends Pivot
{
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public $incrementing = true;
}
