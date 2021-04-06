<?php

namespace App\Http\Controllers\Staff\Places;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Places\PlaceRequest;
use App\Eloquents\Place;

class StoreAction extends Controller
{
    public function __invoke(PlaceRequest $request)
    {
        $validated = $request->validated();

        Place::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'notes' => $validated['notes'],
        ]);

        return redirect()
            ->route('staff.places.create')
            ->with('topAlert.title', '場所を作成しました');
    }
}
