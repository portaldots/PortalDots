<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('v2.staff.contacts.emails.index')
            ->with('contact_emails', ContactEmail::all());
    }
}
