<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Eloquents\ContactCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('v2.staff.contacts.categories.index')
            ->with('categories', ContactCategory::all());
    }
}
