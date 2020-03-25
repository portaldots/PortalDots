<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;

class EditAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        return view('v2.circles.form')
            ->with('circle', $circle);
    }
}
