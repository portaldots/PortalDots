<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tag extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('tag')
            ->logOnly([
                'id',
                'name',
            ])
            ->logOnlyDirty();
    }

    public function circles()
    {
        return $this->belongsToMany(Circle::class)->using(CircleTag::class);
    }

    public function pages()
    {
        return $this->belongsToMany(Page::class, 'page_viewable_tags')
            ->using(PageViewableTag::class);
    }

    public function forms()
    {
        return $this->belongsToMany(Form::class, 'page_viewable_tags')
            ->using(FormAnswerableTag::class);
    }
}
