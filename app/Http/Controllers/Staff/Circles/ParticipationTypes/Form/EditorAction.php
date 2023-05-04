<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes\Form;

use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;

class EditorAction extends Controller
{
    public function __invoke(ParticipationType $participationType)
    {
        return view('staff.circles.participation_types.form.editor')
            ->with('participation_type', $participationType);
    }
}
