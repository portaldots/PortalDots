<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmails;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Emails\EmailsRequest;
use Illuminate\Http\Request;

class UpdateAction extends Controller
{
    public function __invoke(ContactEmails $contact_email, EmailsRequest $request)
    {
        if ($contact_email->email != $request->email) {
            // メール送信処理
        }

        $contact_email->name = $request->name;
        $contact_email->email = $request->email;
        $contact_email->save();

        return redirect()
            ->route('staff.contacts.emails.index')
            ->with('topAlert.title', "「{$contact_email->name}」を変更しました");
    }
}
