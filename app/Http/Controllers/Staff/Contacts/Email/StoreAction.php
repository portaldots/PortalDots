<?php

namespace App\Http\Controllers\Staff\Contacts\Email;

use App\Eloquents\ContactEmail;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Email\EmailRequest;
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

    public function __invoke(EmailRequest $request)
    {

        $email = DB::transaction(function () use ($request) {
            $email = ContactEmail::create([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            $email->save();

            return $email;
        });

        $this->contactsService->sendContactEmail($email);

        return redirect()
            ->route('staff.contacts.email.index')
            ->with('topAlert.title', 'メールアドレスを追加しました');
    }
}
