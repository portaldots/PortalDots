<?php

namespace App\Http\Controllers\Staff\Forms\Copy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Form;

class StoreAction extends Controller
{
    public function __invoke(Form $form)
    {
        $form_copy = $form->replicate()->fill([
            'name' => $form->name . 'のコピー',
            'is_public' => false,
        ]);

        if (! $form_copy->save()) {
            // 保存失敗時の処理
            return 0;
        }

        $questions = $form->questions()->get();

        $questions_copy = $questions->map(function ($question) use ($form_copy) {
            return $question->replicate(['form_id']);
        });

        $form_copy->questions()->createMany($questions_copy->toArray());
    }
}
