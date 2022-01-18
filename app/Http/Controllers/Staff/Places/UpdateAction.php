<?php

namespace App\Http\Controllers\Staff\Places;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Places\PlaceRequest;
use App\Eloquents\Place;

class UpdateAction extends Controller
{
    public function __invoke(PlaceRequest $request, Place $place)
    {
        $validated = $request->validated();

        $place->name = $validated['name'];
        $place->type = $validated['type'];
        $place->notes = $validated['notes'];
        $place->save();

        return redirect()
            ->back()
            ->with('topAlert.title', '場所を更新しました');
    }
}
