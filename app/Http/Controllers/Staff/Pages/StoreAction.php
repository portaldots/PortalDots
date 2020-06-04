<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Pages\PageRequest;

class StoreAction extends Controller
{
    public function __invoke(PageRequest $request)
    {
        $values = $request->validated();
    }
}
