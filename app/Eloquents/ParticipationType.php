<?php

namespace App\Eloquents;

use Database\Factories\ParticipationTypeFactory;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ParticipationType extends Model
{
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ParticipationTypeFactory::new();
    }

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

    protected $appends = ['form_name'];

    public function getFormNameAttribute(): string
    {
        return '企画参加登録';
    }

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

    public function scopeOpen(Builder $query)
    {
        return $query->whereHas('form', function (Builder $query) {
            $query->open();
        });
    }

    public function scopePublic(Builder $query)
    {
        return $query->whereHas('form', function (Builder $query) {
            $query->public();
        });
    }
}
