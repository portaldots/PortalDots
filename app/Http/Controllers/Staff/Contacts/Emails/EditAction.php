<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditAction extends Controller
{
    public function __invoke(ContactEmail $contact_email)
    {
        return view('v2.staff.contacts.emails.form')
            ->with('contact_email', $contact_email);
    }
}
