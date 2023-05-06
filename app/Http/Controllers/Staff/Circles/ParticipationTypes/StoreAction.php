<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Circles\ParticipationTypes\CreateParticipationTypeRequest;
use Illuminate\Support\Facades\DB;

class StoreAction extends Controller
{
    public function __invoke(CreateParticipationTypeRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $validated = $request->validated();

            $adminName = config('portal.admin_name');
            $confirmationMessage = <<< EOT
            ただいま参加登録の内容を確認しています。{$adminName}より指示がある場合は従ってください。また、内容確認のためご連絡を差し上げる場合があります。
            EOT;

            $form = Form::create([
                'name' => '企画参加登録',
                'confirmation_message' => $confirmationMessage,
                'open_at' => now(),
                'close_at' => now()->addMonth(),
                'is_public' => false,
            ]);

            $participation_type = ParticipationType::create([
                'name' => $validated['name'],
                'description' => '',
                'users_count_min' => 1,
                'users_count_max' => 3,
                'form_id' => $form->id,
            ]);

            return redirect()
                ->route('staff.circles.participation_types.edit', [
                    'participation_type' => $participation_type
                ])
                ->with('topAlert.title', '参加種別を作成しました');
        });
    }
}
