<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class PageViewableTag extends Pivot
{
    use LogsActivity;

    protected static $logName = 'page_viewable_tag';

    protected static $logAttributes = [
        'id',
        'page.id',
        'page.title',
        'tag.id',
        'tag.name',
    ];

    protected static $logOnlyDirty = true;

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
