<?php

namespace App\Http\Controllers\Staff\Circles\Terms;

use App\Eloquents\CustomForm;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Circles\TermsRequest;
use Illuminate\Http\Request;

class UpdateAction extends Controller
{
    public function __invoke(TermsRequest $request)
    {
        $form = CustomForm::getFormByType('circle');

        if (empty($form)) {
            abort(404);
        }

        $form->update([
            'description' => $request->description,
        ]);

        return redirect()
            ->route('staff.circles.terms.edit')
            ->with('topAlert.title', '変更を保存しました');
    }
}
