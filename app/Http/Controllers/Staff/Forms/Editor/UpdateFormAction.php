<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Services\Forms\FormEditorService;
use App\Http\Requests\Staff\Forms\Editor\UpdateFormRequest;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;

class UpdateFormAction extends Controller
{
    /**
     * @var FormEditorService
     */
    private $formEditorService;

    public function __construct(FormEditorService $formEditorService)
    {
        $this->formEditorService = $formEditorService;
    }

    public function __invoke(int $form_id, UpdateFormRequest $request)
    {
        $form = $request->form;

        // 参加登録フォームのフォーム情報は修正禁止
        if (isset(Form::findOrFail($form['id'])->participationType)) {
            return abort(400);
        }

        unset($form['created_at'], $form['updated_at'], $form['custom_form'], $form['id']);

        $this->formEditorService->updateForm(
            $form_id,
            $form
        );
    }
}
