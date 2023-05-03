<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;

class DestroyAction extends Controller
{
    public function __invoke(ParticipationType $participationType)
    {
        $participationType->delete();
        return redirect(route('staff.circles.index'));
    }
}
