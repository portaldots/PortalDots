<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmails;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DestroyAction extends Controller
{
    public function __invoke(ContactEmails $contact_email)
    {
        DB::transaction(function () use ($contact_email) {
            $contact_email->delete();
        });

        return redirect()
            ->route('staff.contacts.emails.index')
            ->with('topAlert.title', 'メールアドレスを削除しました');
    }
}
