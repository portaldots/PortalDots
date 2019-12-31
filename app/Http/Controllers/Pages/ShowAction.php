<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;

class ShowAction extends Controller
{
    public function __invoke(Page $page)
    {
        return view('v2.pages.show')
            ->with('page', $page);
    }
}
