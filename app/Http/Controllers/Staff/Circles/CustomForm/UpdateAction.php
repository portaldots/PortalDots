<?php

namespace App\Http\Controllers\Staff\Circles\CustomForm;

use Jackiedo\DotenvEditor\DotenvEditor;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Circles\CustomFormRequest;
use App\Eloquents\CustomForm;
use App\Services\Forms\FormsService;

class UpdateAction extends Controller
{
    /**
     * @var FormsService
     */
    private $formsService;

    /**
     * @var DotenvEditor
     */
    private $editor;

    public function __construct(FormsService $formsService, DotenvEditor $editor)
    {
        $this->formsService = $formsService;
        $this->editor = $editor;
    }

    public function __invoke(CustomFormRequest $request)
    {
        $form = CustomForm::getFormByType('circle');
        if (empty($form)) {
            abort(404);
        }

        $this->editor->setKey('PORTAL_USERS_NUMBER_TO_SUBMIT_CIRCLE', (int)$request->users_number_to_submit_circle);
        $this->editor->save();

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
