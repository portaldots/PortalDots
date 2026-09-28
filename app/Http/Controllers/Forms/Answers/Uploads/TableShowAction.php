<?php

namespace App\Http\Controllers\Forms\Answers\Uploads;

use App\Eloquents\Answer;
use App\Http\Controllers\Controller;
use App\Services\Forms\UploadedFilesService;
use Gate;

class TableShowAction extends Controller
{
    public function __invoke(
        UploadedFilesService $uploadedFilesService,
        int $form_id,
        Answer $answer,
        int $question_id,
        string $row_id,
        string $column_id
    ) {
        $circle = $answer->circle()->first();
        if (Gate::denies('circle.belongsTo', $circle)) {
            abort(404);
        }

        $path = $uploadedFilesService->getPathForTableAnswer(
            $form_id,
            $answer,
            $question_id,
            $row_id,
            $column_id
        );
        abort_if($path === null, 404);

        return response()->download($path);
    }
}
