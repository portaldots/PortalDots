<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Eloquents\Page;
use App\Http\Controllers\Controller;

class DestroyAction extends Controller
{
    public function __invoke(Page $page)
    {
        $page->delete();
        return redirect()
            ->route('staff.pages.index')
            ->with('topAlert.title', 'お知らせを削除しました');
    }
}
