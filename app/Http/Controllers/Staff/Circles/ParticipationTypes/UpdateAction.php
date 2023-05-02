<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Circles\ParticipationTypes\UpdateParticipationTypeRequest;
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
        UpdateParticipationTypeRequest $request
    ) {
        return DB::transaction(function () use ($participationType, $request) {
            $validated = $request->validated();

            $participationType->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
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
                ->route('staff.circles.participation_types.edit', ['participation_type' => $participationType])
                ->with('topAlert.title', '変更を保存しました');
        });
    }
}
