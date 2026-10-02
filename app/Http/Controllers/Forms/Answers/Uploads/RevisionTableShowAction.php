<?php

namespace App\Http\Controllers\Forms\Answers\Uploads;

use Gate;
use App\Http\Controllers\Controller;
use App\Eloquents\Answer;
use App\Eloquents\AnswerRevision;
use App\Services\Forms\UploadedFilesService;

class RevisionTableShowAction extends Controller
{
    public function __invoke(
        UploadedFilesService $uploadedFilesService,
        int $form_id,
        Answer $answer,
        AnswerRevision $revision,
        int $question_id,
        string $row_id,
        string $column_id
    ) {
        $circle = $answer->circle()->first();
        if (Gate::denies('circle.belongsTo', $circle)) {
            abort(404);
        }

        $path = $uploadedFilesService->getPathForRevisionTableAnswer(
            $form_id,
            $answer,
            $revision,
            $question_id,
            $row_id,
            $column_id
        );
        abort_if($path === null, 404);

        return response()->download($path);
    }
}
