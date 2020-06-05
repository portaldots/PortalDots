<?php

namespace App\Http\Controllers\Contacts;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use App\Eloquents\Circle;
use App\Eloquents\ContactEmails;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use App\Services\Contacts\ContactsService;

class PostAction extends Controller
{
    private $contactsService;

    public function __construct(ContactsService $contactsService)
    {
        $this->contactsService = $contactsService;
    }

    public function __invoke(ContactFormRequest $request)
    {
        $circle = !empty($request->circle_id) ? Circle::find($request->circle_id) : null;
        $sender = Auth::user();
        $subject =
            ContactEmails::find($request->subject) ??
            new ContactEmails([
                'email' => config('portal.contact_email'),
                'name' => config('portal.admin_name'),
            ]);

        $this->contactsService->create($circle, $sender, $request->contact_body, $subject);

        return redirect()
            ->route('contacts')
            ->with('topAlert.title', 'お問い合わせを受け付けました。')
            ->with('topAlert.body', new HtmlString(nl2br(e($request->contact_body))));
    }
}
