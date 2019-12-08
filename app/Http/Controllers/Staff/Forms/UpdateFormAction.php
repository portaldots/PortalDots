<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Services\Forms\FormsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdateFormAction extends Controller
{
    private $formsService;

    public function __construct(FormsService $formsService)
    {
        $this->formsService = $formsService;
    }

    // バリデーション、ちゃんとやる
    public function __invoke(int $form_id, Request $request)
    {
        $form = $request->form;
        unset($form['created_at'], $form['updated_at'], $form['id']);

        $this->formsService->updateForm(
            $form_id,
            $form
        );
    }
}
