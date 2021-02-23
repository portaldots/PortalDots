<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Booth extends Pivot
{
    public $incrementing = true;
    public $timestamps = false;
}
