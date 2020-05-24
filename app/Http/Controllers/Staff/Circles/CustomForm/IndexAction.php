<?php

namespace App\Http\Controllers\Staff\Circles\CustomForm;

use Jackiedo\DotenvEditor\DotenvEditor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\CustomForm;

class IndexAction extends Controller
{
    /**
     * @var DotenvEditor
     */
    private $editor;

    public function __construct(DotenvEditor $editor)
    {
        $this->editor = $editor;
    }

    public function __invoke()
    {
        $form = CustomForm::getFormByType('circle');

        if (empty($form)) {
            return view('v2.staff.circles.custom_form.index_not_configured');
        }

        return view('v2.staff.circles.custom_form.index')
            ->with('users_number_to_submit_circle', $this->editor->keyExists('PORTAL_USERS_NUMBER_TO_SUBMIT_CIRCLE') ?
                $this->editor->getValue('PORTAL_USERS_NUMBER_TO_SUBMIT_CIRCLE') : 1)
            ->with('form', $form);
    }
}
