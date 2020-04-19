<?php

namespace App\Http\Controllers\Staff\Circles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\CustomForm;
use App\Eloquents\Tag;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('v2.staff.circles.form')
            ->with('custom_form', CustomForm::getFormByType('circle'))
            ->with('default_tags', \json_encode([]))
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson());
    }
}
