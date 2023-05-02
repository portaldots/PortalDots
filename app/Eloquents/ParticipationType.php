<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ParticipationType extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('participation_type')
            ->logOnly([
                'id',
                'name',
                'description',
                'users_count_min',
                'users_count_max',
                'form_id',
            ])
            ->logOnlyDirty();
    }

    protected $fillable = [
        'name',
        'description',
        'users_count_min',
        'users_count_max',
        'form_id',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function circles()
    {
        return $this->hasMany(Circle::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
