<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('v2.staff.documents.form');
    }
}
