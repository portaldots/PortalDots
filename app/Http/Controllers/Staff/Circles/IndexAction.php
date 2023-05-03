<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.circles.index')
            ->with('participation_types', ParticipationType::with('form')->get());
    }
}
