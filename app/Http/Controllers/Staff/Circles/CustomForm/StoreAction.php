<?php

namespace App\Http\Controllers\Staff\Circles\CustomForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;

class StoreAction extends Controller
{
    public function __invoke()
    {
        return DB::transaction(function () {
            if (!empty(CustomForm::getFormByType('circle'))) {
                return redirect()
                    ->route('staff.circles.custom_form.index')
                    ->with('topAlert.type', 'danger')
                    ->with('topAlert.title', 'すでに企画参加登録機能は有効になっています');
            }

            $form = Form::create([
                'name' => '企画参加登録',
                'open_at' => now()->addWeek(),
                'close_at' => now()->addWeek()->addMonth(),
                'created_by' => Auth::id(),
                'is_public' => false,
            ]);

            CustomForm::create([
                'type' => 'circle',
                'form_id' => $form->id,
            ]);

            return redirect()
                    ->route('staff.circles.custom_form.index')
                    ->with('topAlert.title', '企画参加登録機能を有効にしました');
        });
    }
}
