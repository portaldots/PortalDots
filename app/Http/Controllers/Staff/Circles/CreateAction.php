<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Http\Controllers\Controller;
use App\Eloquents\ParticipationType;
use App\Eloquents\Place;
use App\Eloquents\Tag;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.circles.form')
            ->with('participation_types', ParticipationType::all('id', 'name'))
            ->with('default_places', \json_encode([]))
            ->with('places_autocomplete_items', Place::get()->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('default_tags', \json_encode([]))
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson());
    }
}
