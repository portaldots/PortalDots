<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Contracts\AudiencePolicy;
use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Tag;

class EditAction extends Controller
{
    public function __invoke(Document $document, AudiencePolicy $audiencePolicy)
    {
        return view('staff.documents.form')
            ->with('document', $document)
            ->with('default_tags', $document->viewableTags->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('default_circles', $document->viewableCircles->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('circles_autocomplete_items', Circle::get()->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('allowed_audiences', $audiencePolicy->allowedAudiences());
    }
}
