<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DocumentViewableTag extends Pivot
{
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public $incrementing = true;
}
