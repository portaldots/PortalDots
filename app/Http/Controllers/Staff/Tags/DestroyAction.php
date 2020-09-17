<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Tag;

class DestroyAction extends Controller
{
    public function __invoke(Tag $tag)
    {
        $tag->delete();
        return redirect('/home_staff/tags');
    }
}
