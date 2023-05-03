<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Circles\ParticipationTypes\UpdateParticipationTypeRequest;
use App\Services\Forms\FormEditorService;
use App\Services\ParticipationTypes\ParticipationTypesService;
use App\Services\Tags\Exceptions\DenyCreateTagsException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateAction extends Controller
{
    public function __construct(
        private FormEditorService $formEditorService,
        private ParticipationTypesService $participationTypesService
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
                'description' => $validated['description'] ?? '',
            ]);

            // タグの保存
            try {
                $this->participationTypesService->saveTags(
                    $participationType,
                    $request->tags ?? [],
                    Auth::user()->can('staff.tags.edit'),
                    Auth::user()
                );
            } catch (DenyCreateTagsException $e) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['tags' => $e->getMessage()]);
            }

            return redirect()
                ->route('staff.circles.participation_types.edit', ['participation_type' => $participationType])
                ->with('topAlert.title', '変更を保存しました');
        });
    }
}
