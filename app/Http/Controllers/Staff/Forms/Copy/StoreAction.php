<?php

namespace App\Http\Controllers\Staff\Forms\Copy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Form;
use Illuminate\Support\Facades\Auth;

class StoreAction extends Controller
{
    public function __invoke(Form $form)
    {
        $form_copy = $form->replicate()->fill([
            'name' => $form->name . 'のコピー',
            'created_by' => Auth::id(),
            'is_public' => false,
        ]);

        if (! $form_copy->save()) {
            return redirect()
                ->route('staff.forms.copy', ['form' => $form])
                ->with('topAlert.title', '保存に失敗しました')
                ->with('topAlert.type', 'danger');
        }

        $questions = $form->questions()->get();

        $questions_copy = $questions->map(function ($question) use ($form_copy) {
            return $question->replicate(['form_id']);
        });

        $form_copy->questions()->createMany($questions_copy->toArray());

        return redirect("/home_staff/applications/read/{$form_copy->id}");
    }
}
