<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DestroyAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $circle->delete();
        return redirect(route('staff.circles.index'));
    }
}
