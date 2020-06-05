<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmails;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditAction extends Controller
{
    public function __invoke(ContactEmails $contact_email)
    {
        return view('v2.staff.contacts.emails.form')
            ->with('email', $contact_email);
    }
}
