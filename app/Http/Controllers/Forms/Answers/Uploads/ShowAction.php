<?php

namespace App\Http\Controllers\Forms\Answers\Uploads;

use Gate;
use App\Http\Controllers\Controller;
use App\Eloquents\Answer;
use App\Services\Forms\UploadedFilesService;

class ShowAction extends Controller
{
    public function __invoke(
        UploadedFilesService $uploadedFilesService,
        int $form_id,
        Answer $answer,
        int $question_id
    ) {
        $circle = $answer->circle()->first();
        if (Gate::denies('circle.belongsTo', $circle)) {
            abort(404);
        }

        $path = $uploadedFilesService->getPathForAnswer($form_id, $answer, $question_id);
        abort_if($path === null, 404);

        return response()->download($path);
    }
}
