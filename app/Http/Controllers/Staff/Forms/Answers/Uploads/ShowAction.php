<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

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
        $path = $uploadedFilesService->getPathForAnswer($form_id, $answer, $question_id);
        abort_if($path === null, 404);

        return response()->file($path);
    }
}
