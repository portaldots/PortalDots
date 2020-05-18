<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FormsService
{
    /**
     * フォームを更新する
     *
     * @param int $form_id フォームID
     * @param array $form フォーム情報配列
     */
    public function updateForm(int $form_id, array $form)
    {
        $eloquent = Form::findOrFail($form_id);
        $form['open_at'] = new Carbon($form['open_at']);
        $form['close_at'] = new Carbon($form['close_at']);
        $eloquent->fill($form);
        $eloquent->save();
    }

    /**
     * フォームを複製する
     *
     * @param Form $form
     * @return Form|null
     */
    public function copyForm(Form $form)
    {
        $form_copy = $form->replicate()->fill([
            'name' => $form->name . 'のコピー',
            'created_by' => Auth::id(),
            'is_public' => false,
        ]);

        if ($form_copy->save()) {
            $questions = $form->questions()->get();
            $questions_copy = $questions->map(function ($question) use ($form_copy) {
                return $question->replicate(['form_id']);
            });

            $form_copy->questions()->createMany($questions_copy->toArray());

            return $form_copy;
        }

        return null;
    }
}
