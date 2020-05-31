<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
        $form_copy = $this->formsService->copyForm($form);

        return redirect("/home_staff/applications/read/{$form_copy->id}?copied=1");
    }
}
