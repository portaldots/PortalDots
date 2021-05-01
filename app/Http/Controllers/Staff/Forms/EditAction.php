<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Tag;

class EditAction extends Controller
{
    public function __invoke(Form $form)
    {
        // カスタムフォームのフォーム情報は修正禁止
        if (isset($form->customForm)) {
            return abort(400);
        }
        return view('staff.forms.form')
            ->with('form', $form)
            ->with('default_tags', $form->answerableTags->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson());
    }
}
