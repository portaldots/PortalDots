<?php

namespace App\Http\Controllers\Staff\Circles\Export;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('v2.staff.circles.export.index');
    }
}
