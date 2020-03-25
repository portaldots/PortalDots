<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Eloquents\Circle;
use App\Eloquents\User;

class CircleUser extends Pivot
{
    public $incrementing = true;
}
