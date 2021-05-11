<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Tag extends Model
{
    use LogsActivity;

    protected static $logName = 'tag';

    protected static $logAttributes = [
        'id',
        'name',
    ];

    protected static $logOnlyDirty = true;

    protected $fillable = [
        'name'
    ];

    public function circles()
    {
        return $this->belongsToMany(Circle::class);
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
