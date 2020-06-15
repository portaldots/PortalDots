<?php

namespace App\Http\Controllers\Staff\Contacts\Email;

use App\Eloquents\ContactEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Email\EmailRequest;
use App\Services\Contacts\ContactsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UpdateAction extends Controller
{

    /**
     * @var contactsService
     */
    private $contactsService;

    public function __construct(ContactsService $contactsService)
    {
        $this->contactsService = $contactsService;
    }

    public function __invoke(ContactEmail $contact_email, EmailRequest $request)
    {
        $old_email = $contact_email->email;

        DB::transaction(function () use ($request, $contact_email) {
            $contact_email->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        });

        if ($old_email != $request->email) {
            $this->contactsService->sendContactEmail($contact_email);
        }

        return redirect()
            ->route('staff.contacts.email.index')
            ->with('topAlert.title', "「{$contact_email->name}」を変更しました");
    }
}
