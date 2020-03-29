<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;

class IndexAction extends Controller
{
    public function __invoke()
    {
        $pages = Page::paginate(10);

        if ($pages->currentPage() > $pages->lastPage()) {
            return redirect($pages->url($pages->lastPage()));
        }

        return view('v2.pages.index')
            ->with('pages', $pages);
    }
}
