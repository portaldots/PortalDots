<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Services\Forms\FormsService;
use Illuminate\Support\Facades\Auth;

class CopyAction extends Controller
{
    public $formsService;

    public function __construct(FormsService $formsService)
    {
        $this->formsService = $formsService;
    }

    public function __invoke(Form $form)
    {
        $this->formsService->copyForm($form, Auth::user());

        return redirect()
            ->route('staff.forms.index')
            ->with('topAlert.title', 'フォームを複製しました');
    }
}
