<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Services\Forms\FormsService;

class CopyAction extends Controller
{
    /**
     * @var FormsService
     */
    public $formsService;

    public function __construct(FormsService $formsService)
    {
        $this->formsService = $formsService;
    }

    public function __invoke(Form $form)
    {
        $this->formsService->copyForm($form);

        return redirect()
            ->route('staff.forms.index')
            ->with('topAlert.title', 'フォームを複製しました');
    }
}
