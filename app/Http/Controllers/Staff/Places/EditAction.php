<?php

namespace App\Http\Controllers\Staff\Places;

use App\Http\Controllers\Controller;
use App\Eloquents\Place;

class EditAction extends Controller
{
    public function __invoke(Place $place)
    {
        return view('staff.places.form')
            ->with('place', $place);
    }
}
