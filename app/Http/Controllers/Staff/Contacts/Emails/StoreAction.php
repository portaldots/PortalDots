<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmails;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Emails\EmailsRequest;
use Illuminate\Http\Request;

class StoreAction extends Controller
{
    public function __invoke(EmailsRequest $request)
    {
        $email = ContactEmails::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        $email->save();


        return redirect()
            ->route('staff.contacts.emails.index')
            ->with('topAlert.title', '保存しました');
    }
}
