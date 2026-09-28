<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Forms\Assignments;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use App\Services\Forms\FormAssignmentsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UpdateAction extends Controller
{
    /**
     * @var FormAssignmentsService
     */
    private $formAssignmentsService;

    public function __construct(FormAssignmentsService $formAssignmentsService)
    {
        $this->formAssignmentsService = $formAssignmentsService;
    }

    public function __invoke(Request $request, Form $form, Circle $circle)
    {
        $values = $request->validate([
            'due_at' => ['nullable', 'date'],
        ]);

        $this->formAssignmentsService->updateDueDate(
            $form,
            $circle,
            empty($values['due_at']) ? null : new Carbon($values['due_at'])
        );

        return redirect()
            ->route('staff.forms.edit', ['form' => $form])
            ->with('topAlert.title', '期限を変更しました');
    }
}
