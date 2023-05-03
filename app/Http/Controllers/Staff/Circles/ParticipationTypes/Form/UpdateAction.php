<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes\Form;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Circles\ParticipationTypes\ParticipationFormRequest;
use App\Services\Forms\FormEditorService;
use Illuminate\Support\Facades\DB;

class UpdateAction extends Controller
{
    public function __construct(
        private FormEditorService $formEditorService
    ) {
    }

    public function __invoke(
        ParticipationType $participationType,
        ParticipationFormRequest $request
    ) {
        return DB::transaction(function () use ($participationType, $request) {
            $validated = $request->validated();

            $participationType->update([
                'users_count_min' => intval($validated['users_count_min']),
                'users_count_max' => intval($validated['users_count_max']),
            ]);

            $this->formEditorService->updateForm($participationType->form->id, [
                'open_at' => $validated['open_at'],
                'close_at' => $validated['close_at'],
                'is_public' => $validated['is_public'] ?? false,
                'description' => $validated['form_description'],
            ]);

            return redirect()
                ->route('staff.circles.participation_types.form.edit', ['participation_type' => $participationType])
                ->with('topAlert.title', '変更を保存しました');
        });
    }
}
