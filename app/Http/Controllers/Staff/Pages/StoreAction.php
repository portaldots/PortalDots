<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreAction extends Controller
{
    public function __invoke()
    {
        return response('hello');
    }
}
