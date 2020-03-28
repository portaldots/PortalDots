<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GetFormAction extends Controller
{
    public function __invoke(int $form_id)
    {
        $form = Form::withoutGlobalScope('withoutCustomForms')->findOrFail($form_id);

        return [
            'id' => $form->id,
            'name' => $form->name,
            'description' => $form->description,
            'open_at' => $form->open_at,
            'close_at' => $form->close_at,
            'created_by' => $form->created_by,
            'type' => $form->type,
            'max_answers' => $form->max_answers,
            'is_public' => $form->is_public,
            'created_at' => $form->created_at,
            'updated_at' => $form->updated_at,
            'custom_form' => $form->customForm,
        ];
    }
}
