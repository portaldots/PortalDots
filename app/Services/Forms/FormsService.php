<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\User;
use Illuminate\Support\Facades\DB;

class FormsService
{
    /**
     * フォームを複製する
     *
     * @param Form $form
     * @param User $user フォームの作成者とするユーザー
     * @return Form|null
     */
    public function copyForm(Form $form, User $user)
    {
        return DB::transaction(function () use ($form, $user) {
            $form_copy = $form->replicate()->fill([
                'name' => $form->name . 'のコピー',
                'created_by' => $user->id,
                'is_public' => false,
            ]);

            $form_copy->save();

            $questions = $form->questions()->get();
            $questions_copy = $questions->map(function ($question) {
                return $question->replicate(['form_id']);
            });

            $form_copy->questions()->createMany($questions_copy->toArray());
            return $form_copy;
        });

        return null;
    }
}
