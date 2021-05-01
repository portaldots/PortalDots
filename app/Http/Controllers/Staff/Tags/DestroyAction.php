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
        return redirect()
            ->route('staff.tags.index')
            ->with('topAlert.title', 'タグを削除しました');
    }
}
