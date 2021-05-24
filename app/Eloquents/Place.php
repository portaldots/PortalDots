<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Place extends Model
{
    use LogsActivity;

    protected static $logName = 'place';

    protected static $logAttributes = [
        'id',
        'name',
        'type',
        'notes',
    ];

    protected static $logOnlyDirty = true;

    protected $fillable = [
        'name',
        'type',
        'notes',
    ];

    protected $casts = [
        'type' => 'int',
    ];

    public function circles()
    {
        return $this->belongsToMany(Circle::class, 'booths')->using(Booth::class);
    }
}
