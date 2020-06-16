<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Tag;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('v2.staff.pages.form')
            ->with('default_tags', \json_encode([]))
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson());
    }
}
