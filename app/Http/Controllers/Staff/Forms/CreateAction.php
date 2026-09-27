<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Contracts\AudiencePolicy;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Tag;

class CreateAction extends Controller
{
    public function __invoke(AudiencePolicy $audiencePolicy)
    {
        return view('staff.forms.form')
            ->with('default_tags', \json_encode([]))
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('allowed_audiences', Form::allowedAudiences($audiencePolicy));
    }
}
