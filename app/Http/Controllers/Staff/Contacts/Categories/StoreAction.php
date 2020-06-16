<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Categories\CategoryRequest;
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

    public function __invoke(CategoryRequest $request)
    {

        $category = DB::transaction(function () use ($request) {
            $category = ContactCategory::create([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            $category->save();

            return $category;
        });

        $this->contactsService->sendContactCategory($category);

        return redirect()
            ->route('staff.contacts.categories.index')
            ->with('topAlert.title', 'メールアドレスを追加しました');
    }
}
