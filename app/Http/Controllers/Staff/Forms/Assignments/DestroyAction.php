<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Forms\Assignments;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use App\Services\Forms\FormAssignmentsService;

class DestroyAction extends Controller
{
    /**
     * @var FormAssignmentsService
     */
    private $formAssignmentsService;

    public function __construct(FormAssignmentsService $formAssignmentsService)
    {
        $this->formAssignmentsService = $formAssignmentsService;
    }

    public function __invoke(Form $form, Circle $circle)
    {
        $this->formAssignmentsService->unassign($form, $circle);

        return redirect()
            ->route('staff.forms.edit', ['form' => $form])
            ->with('topAlert.title', '送付先から削除しました');
    }
}
