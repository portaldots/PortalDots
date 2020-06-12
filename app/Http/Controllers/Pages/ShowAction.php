<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;

class ShowAction extends Controller
{
    public function __invoke(Page $page)
    {
        if ($page->usersWhoRead()->where('user_id', Auth::id())->doesntExist()) {
            $page->usersWhoRead()->attach(Auth::id(), ['created_at' => now()]);
        }

        return view('v2.pages.show')
            ->with('page', $page);
    }
}
