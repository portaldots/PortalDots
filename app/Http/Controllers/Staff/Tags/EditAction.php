<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\Controller;
use App\Eloquents\Tag;

class EditAction extends Controller
{
    public function __invoke(Tag $tag)
    {
        return view('staff.tags.form')
            ->with('tag', $tag);
    }
}
