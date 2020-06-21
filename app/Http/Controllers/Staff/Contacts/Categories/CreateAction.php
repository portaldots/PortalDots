<?php

namespace App\Http\Controllers\Staff\Contacts\Categories;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.contacts.categories.form');
    }
}
