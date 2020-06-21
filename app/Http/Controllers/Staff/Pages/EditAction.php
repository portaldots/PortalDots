<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Page;
use App\Eloquents\Tag;

class EditAction extends Controller
{
    public function __invoke(Page $page)
    {
        return view('staff.pages.form')
            ->with('page', $page)
            ->with('default_tags', $page->viewableTags->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson());
    }
}
