<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Forms\Assignments;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use App\Services\Forms\FormAssignmentsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreAction extends Controller
{
    /**
     * @var FormAssignmentsService
     */
    private $formAssignmentsService;

    public function __construct(FormAssignmentsService $formAssignmentsService)
    {
        $this->formAssignmentsService = $formAssignmentsService;
    }

    public function __invoke(Request $request, Form $form)
    {
        if (isset($form->participationType)) {
            abort(400);
        }

        $values = $request->validate([
            'circles' => ['required', 'array'],
            'circles.*' => ['integer', 'exists:circles,id'],
            'due_at' => ['nullable', 'date'],
        ]);

        $this->formAssignmentsService->assignToCircles(
            $form,
            $values['circles'],
            empty($values['due_at']) ? null : new Carbon($values['due_at']),
            Auth::user()
        );

        return redirect()
            ->route('staff.forms.edit', ['form' => $form])
            ->with('topAlert.title', '送付先の企画を追加しました');
    }
}
