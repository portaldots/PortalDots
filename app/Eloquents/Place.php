<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Place extends Model
{
    use LogsActivity;

    protected static $logName = 'place';

    protected static $logAttributes = ['id', 'name', 'type', 'notes'];

    protected static $logOnlyDirty = true;

    protected $fillable = ['name', 'type', 'notes'];

    protected $casts = [
        'type' => 'int',
    ];

    public function circles()
    {
        return $this->belongsToMany(Circle::class, 'booths')->using(
            Booth::class
        );
    }

    public function getTypeName()
    {
        if ($this->type === 1) {
            return '屋内';
        }
        if ($this->type === 2) {
            return '屋外';
        }
        return '特殊場所';
    }
}
