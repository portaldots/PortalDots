<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditAction extends Controller
{
    public function __invoke(ParticipationType $participationType)
    {
        return view('staff.circles.participation_types.edit')
            ->with('participation_type', $participationType);
    }
}
