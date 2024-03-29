<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke(ParticipationType $participationType)
    {
        $participationType->loadMissing(['form', 'form.questions']);

        return view('staff.circles.data_grid')
            ->with('participation_type', $participationType);
    }
}
