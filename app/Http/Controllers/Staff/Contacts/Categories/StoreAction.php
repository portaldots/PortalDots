<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Categories\CategoryRequest;
use App\Services\Contacts\ContactCategoriesService;
use Illuminate\Http\Request;

class StoreAction extends Controller
{

    /**
     * @var ContactCategoriesService
     */
    private $categoriesService;

    public function __construct(ContactCategoriesService $categoriesService)
    {
        $this->categoriesService = $categoriesService;
    }

    public function __invoke(CategoryRequest $request)
    {

        $category = ContactCategory::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $category->save();

        $this->categoriesService->send($category);

        return redirect()
            ->route('staff.contacts.categories.index')
            ->with('topAlert.title', 'メールアドレスを追加しました');
    }
}
