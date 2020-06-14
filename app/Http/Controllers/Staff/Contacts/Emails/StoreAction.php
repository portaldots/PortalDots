<?php

namespace App\Http\Controllers\Staff\Contacts\Emails;

use App\Eloquents\ContactEmails;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Emails\EmailsRequest;
use App\Mail\Contacts\EmailsMailable;
use App\Services\Contacts\ContactsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreAction extends Controller
{

    private $contactsService;

    public function __construct(ContactsService $contactsService)
    {
        $this->contactsService = $contactsService;
    }

    public function __invoke(EmailsRequest $request)
    {

        $email = DB::transaction(function () use ($request) {
            $email = ContactEmails::create([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            $email->save();

            return $email;
        });

        $this->contactsService->sendContactEmail($email);

        return redirect()
            ->route('staff.contacts.emails.index')
            ->with('topAlert.title', '保存しました');
    }
}
