<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditAction extends Controller
{
    public function __invoke(ContactCategory $category)
    {
        return view('v2.staff.contacts.categories.form')
            ->with('category', $category);
    }
}
