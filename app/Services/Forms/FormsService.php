<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use Carbon\Carbon;

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
        $eloquent = Form::withoutGlobalScope('withoutCustomForms')->findOrFail($form_id);
        $form['open_at'] = new Carbon($form['open_at']);
        $form['close_at'] = new Carbon($form['close_at']);
        $eloquent->fill($form);
        $eloquent->save();
    }
}
