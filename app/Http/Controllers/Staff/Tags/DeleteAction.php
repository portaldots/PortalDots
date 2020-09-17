<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Tag;

class DeleteAction extends Controller
{
    public function __invoke(Tag $tag)
    {
        return view('staff.tags.delete')
            ->with('tag', $tag);
    }
}
