<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Contracts\AudiencePolicy;
use App\Http\Controllers\Controller;
use App\Eloquents\Page;
use App\Eloquents\Circle;
use App\Eloquents\Tag;
use App\Eloquents\Document;

class EditAction extends Controller
{
    public function __invoke(Page $page, AudiencePolicy $audiencePolicy)
    {
        return view('staff.pages.form')
            ->with('page', $page)
            ->with('default_tags', $page->viewableTags->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('default_documents', $page->documents->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('documents_autocomplete_items', Document::get()->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('default_circles', $page->viewableCircles->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('circles_autocomplete_items', Circle::get()->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('allowed_audiences', $audiencePolicy->allowedAudiences())
            ->with('allows_tag_targets', $audiencePolicy->allowsTagTargets());
    }
}
