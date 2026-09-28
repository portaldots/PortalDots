<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Contracts\AudiencePolicy;
use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\Tag;

class CreateAction extends Controller
{
    public function __invoke(AudiencePolicy $audiencePolicy)
    {
        return view('staff.documents.form')
            ->with('default_tags', \json_encode([]))
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('default_circles', \json_encode([]))
            ->with('circles_autocomplete_items', Circle::get()->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('allowed_audiences', $audiencePolicy->allowedAudiences())
            ->with('allows_tag_targets', $audiencePolicy->allowsTagTargets());
    }
}
