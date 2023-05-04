<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.circles.participation_types.create');
    }
}
