<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Categories\CategoryRequest;
use App\Services\Contacts\ContactsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UpdateAction extends Controller
{

    /**
     * @var ContactsService
     */
    private $contactsService;

    public function __construct(ContactsService $contactsService)
    {
        $this->contactsService = $contactsService;
    }

    public function __invoke(ContactCategory $category, CategoryRequest $request)
    {
        $old_email = $category->email;

        DB::transaction(function () use ($request, $category) {
            $category->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        });

        if ($old_email !== $request->email) {
            $this->contactsService->sendContactCategory($category);
        }

        return redirect()
            ->route('staff.contacts.categories.index')
            ->with('topAlert.title', "「{$category->name}」を変更しました");
    }
}
