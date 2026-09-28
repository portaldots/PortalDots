<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

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
        $path = $uploadedFilesService->getPathForRevisionTableAnswer(
            $form_id,
            $answer,
            $revision,
            $question_id,
            $row_id,
            $column_id
        );
        abort_if($path === null, 404);

        return response()->file($path);
    }
}
