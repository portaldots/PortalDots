<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Eloquents\Page;
use App\Eloquents\Schedule;
use App\Eloquents\Document;

class HomeAction extends Controller
{
    public function __invoke()
    {
        return view('v2.home')
            ->with('my_circles', Auth::user()->circles)
            ->with('pages', Page::take(5)->get())
            ->with('remaining_pages_count', max(Page::count() - 5, 0))
            ->with('next_schedule', Schedule::startOrder()->notStarted()->first())
            ->with('documents', Document::take(5)->public()->with('schedule')->get())
            ->with('remaining_documents_count', max(Document::public()->count() - 5, 0));
    }
}
