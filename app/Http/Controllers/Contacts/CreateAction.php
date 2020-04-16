<?php

namespace App\Http\Controllers\Contacts;

use App\Eloquents\Circle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('v2.contacts.form')
            ->with('circles', Auth::user()->circles()->get());
    }
}
