<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;

class GetFormAction extends Controller
{
    public function __invoke(Form $form)
    {
        return [
            'id' => $form->id,
            'name' => $form->name,
            'description' => $form->description,
            'open_at' => $form->open_at,
            'close_at' => $form->close_at,
            'type' => $form->type,
            'max_answers' => $form->max_answers,
            'is_public' => $form->is_public,
            'created_at' => $form->created_at,
            'updated_at' => $form->updated_at,
            'custom_form' => $form->customForm,
            'demo_mode' => config('portal.enable_demo_mode'),
        ];
    }
}
