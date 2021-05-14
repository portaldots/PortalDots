<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\Activitylog\Traits\LogsActivity;

class CircleUser extends Pivot
{
    use LogsActivity;

    protected static $logName = 'circle_user';

    protected static $logAttributes = [
        'id',
        'circle.id',
        'circle.name',
        'user.id',
        'user.student_id',
        'user.name_family',
        'user.name_given',
        'is_leader',
        'notes',
    ];

    protected static $logOnlyDirty = true;

    public $incrementing = true;

    protected $casts = [
        'is_leader' => 'bool',
    ];

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
