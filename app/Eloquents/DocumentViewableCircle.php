<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DocumentViewableCircle extends Pivot
{
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public $incrementing = true;
}
