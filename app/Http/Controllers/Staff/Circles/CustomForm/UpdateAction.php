<?php

namespace App\Http\Controllers\Staff\Circles\CustomForm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Circles\CustomFormRequest;
use App\Eloquents\CustomForm;
use App\Services\Forms\FormsService;

class UpdateAction extends Controller
{
    private $formsService;

    public function __construct(FormsService $formsService)
    {
        $this->formsService = $formsService;
    }

    public function __invoke(CustomFormRequest $request)
    {
        $form = CustomForm::getFormByType('circle');
        if (empty($form)) {
            abort(404);
        }
        $this->formsService->updateForm($form->id, [
            'open_at' => $request->open_at,
            'close_at' => $request->close_at,
            'is_public' => $request->is_public ?? false,
        ]);

        return redirect()
            ->route('staff.circles.custom_form.index')
            ->with('topAlert.title', '変更を保存しました');
    }
}
