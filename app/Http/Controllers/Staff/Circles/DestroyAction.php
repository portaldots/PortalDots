<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;

class DestroyAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $participationType = $circle->participationType;
        $circle->delete();
        return redirect(route('staff.circles.participation_types.index', [
            'participation_type' => $participationType
        ]));
    }
}
