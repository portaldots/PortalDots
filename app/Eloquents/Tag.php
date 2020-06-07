<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
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
}
