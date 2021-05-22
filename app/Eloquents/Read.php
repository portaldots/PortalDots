<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Read extends Pivot
{
    protected $table = 'reads';
    public $incrementing = true;
}
