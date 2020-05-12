<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Services\Forms\FormsService;
use App\Http\Requests\Staff\Forms\Editor\UpdateFormRequest;
use App\Http\Controllers\Controller;

class UpdateFormAction extends Controller
{
    /**
     * @var FormsService
     */
    private $formsService;

    public function __construct(FormsService $formsService)
    {
        $this->formsService = $formsService;
    }

    public function __invoke(int $form_id, UpdateFormRequest $request)
    {
        $form = $request->form;
        unset($form['created_by'], $form['created_at'], $form['updated_at'], $form['custom_form'], $form['id']);

        $this->formsService->updateForm(
            $form_id,
            $form
        );
    }
}
