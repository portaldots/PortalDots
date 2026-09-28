<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Contracts\AudiencePolicy;
use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Tag;

class EditAction extends Controller
{
    public function __invoke(Form $form, AudiencePolicy $audiencePolicy)
    {
        // 参加登録フォームのフォーム情報は修正禁止
        if (isset($form->participationType)) {
            return abort(400);
        }

        $assignments = $form->assignments()->with('circle')->orderBy('id')->get();

        return view('staff.forms.form')
            ->with('form', $form)
            ->with('default_tags', $form->answerableTags->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('circles_autocomplete_items', Circle::get()->map(function ($item) {
                return ['text' => $item->name, 'value' => $item->id];
            })->toJson())
            ->with('assignments', $assignments)
            ->with('allowed_audiences', Form::allowedAudiences($audiencePolicy))
            ->with('allows_tag_targets', $audiencePolicy->allowsTagTargets());
    }
}
