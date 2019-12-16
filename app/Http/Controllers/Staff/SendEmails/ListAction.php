<?php

namespace App\Http\Controllers\Staff\SendEmails;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;

class ListAction extends Controller
{
    public function __invoke()
    {
        return view('staff.send_emails.list')
            ->with('pages', Page::all());
    }
}
