<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Contacts\Categories\CategoryRequest;
use App\Services\Contacts\ContactCategoriesService;
use Illuminate\Http\Request;

class UpdateAction extends Controller
{
    /**
     * @var ContactCategoriesService
     */
    private $categoriesService;

    public function __construct(ContactCategoriesService $categoriesService)
    {
        $this->categoriesService = $categoriesService;
    }

    public function __invoke(ContactCategory $category, CategoryRequest $request)
    {
        $old_email = $category->email;

        $category->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($old_email !== $request->email) {
            $this->categoriesService->send($category);
        }

        return redirect()
            ->route('staff.contacts.categories.index')
            ->with('topAlert.title', "「{$category->name}」を変更しました");
    }
}
