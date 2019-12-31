<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Eloquents\Page;
use App\Eloquents\Schedule;
use App\Eloquents\Document;

class HomeAction extends Controller
{
    public function __invoke()
    {
        return view('v2.home')
            ->with('pages', Page::take(5)->get())
            ->with('next_schedule', Schedule::startOrder()->notStarted()->first())
            ->with('documents', Document::public()->get());
    }
}
