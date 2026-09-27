<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

use App\Eloquents\Answer;
use App\Http\Controllers\Controller;
use App\Services\Forms\UploadedFilesService;

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
        $path = $uploadedFilesService->getPathForTableAnswer(
            $form_id,
            $answer,
            $question_id,
            $row_id,
            $column_id
        );
        abort_if($path === null, 404);

        return response()->file($path);
    }
}
