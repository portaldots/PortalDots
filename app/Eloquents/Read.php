<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Eloquents\Page;
use App\Eloquents\User;

class Read extends Pivot
{
    public $incrementing = true;
}
