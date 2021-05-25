<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class PageViewableTag extends Pivot
{
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public $incrementing = true;
}
