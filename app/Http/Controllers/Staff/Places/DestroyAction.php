<?php

namespace App\Http\Controllers\Staff\Places;

use App\Http\Controllers\Controller;
use App\Eloquents\Place;

class DestroyAction extends Controller
{
    public function __invoke(Place $place)
    {
        $place->delete();
        return redirect()
            ->route('staff.places.index')
            ->with('topAlert.title', '場所を削除しました');
    }
}
