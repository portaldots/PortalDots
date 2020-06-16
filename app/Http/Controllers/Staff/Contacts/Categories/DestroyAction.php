<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DestroyAction extends Controller
{
    public function __invoke(ContactCategory $category)
    {
        DB::transaction(function () use ($category) {
            $category->delete();
        });

        return redirect()
            ->route('staff.contacts.categories.index')
            ->with('topAlert.title', 'メールアドレスを削除しました');
    }
}
