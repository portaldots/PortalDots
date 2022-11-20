<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Place extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'notes',
    ];

    protected $casts = [
        'type' => 'int',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('place')
            ->logOnly([
                'id',
                'name',
                'type',
                'notes',
            ])
            ->logOnlyDirty();
    }

    public function circles()
    {
        return $this->belongsToMany(Circle::class, 'booths')->using(Booth::class);
    }
}
