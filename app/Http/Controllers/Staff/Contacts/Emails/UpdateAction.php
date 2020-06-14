<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Emails\EmailsRequest;
use App\Mail\Contacts\EmailsMailable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UpdateAction extends Controller
{
    public function __invoke(ContactEmail $contact_email, EmailsRequest $request)
    {
        $old_email = $contact_email->email;

        DB::transaction(function () use ($request, $contact_email) {
            $contact_email->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        });

        if ($old_email != $request->email) {
            Mail::to($contact_email->email, $contact_email->name)
                ->send(
                    (new EmailsMailable($contact_email))
                        ->subject('お問い合せ先に設定されました')
                );
        }

        return redirect()
            ->route('staff.contacts.emails.index')
            ->with('topAlert.title', "「{$contact_email->name}」を変更しました");
    }
}
