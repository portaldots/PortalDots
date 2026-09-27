<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Services\Forms\DownloadZipService;
use App\Services\Forms\Exceptions\NoDownloadFileExistException;
use App\Services\Forms\Exceptions\ZipArchiveNotSupportedException;
use App\Services\Forms\AnswerDetailsService;

class DownloadZipAction extends Controller
{
    /**
     * @var DownloadZipService
     */
    private $downloadZipService;

    public function __construct(DownloadZipService $downloadZipService)
    {
        $this->downloadZipService = $downloadZipService;
    }

    public function __invoke(Form $form)
    {
        $form->load('answers.details');
        $form->load('answers.circle');
        $form->load(['questions' => function ($query) {
            $query->whereIn('type', ['upload', 'table']);
        }]);

        $questions = $form->questions->keyBy('id');
        $flatten_details = $form->answers->filter(function ($answer) {
            return !empty($answer->circle->submitted_at);
        })->pluck('details')->flatten();

        $uploaded_file_paths = [];

        foreach ($flatten_details as $detail) {
            $question = $questions->get($detail->question_id);
            if ($question === null) {
                continue;
            }
            if ($question->type === 'upload') {
                $uploaded_file_paths[] = $detail->answer;
                continue;
            }

            $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($detail->answer);
            $uploadColumnIds = collect($envelope['columns'])
                ->filter(fn ($column) => ($column['type'] ?? null) === 'upload')
                ->pluck('id')
                ->all();
            foreach ($envelope['rows'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                foreach ($uploadColumnIds as $columnId) {
                    if (isset($row[$columnId]) && is_string($row[$columnId])) {
                        $uploaded_file_paths[] = $row[$columnId];
                    }
                }
            }
        }

        try {
            $zip_path = $this->downloadZipService->makeZip($form, $uploaded_file_paths);
            return response()->download($zip_path)->deleteFileAfterSend(true);
        } catch (NoDownloadFileExistException $e) {
            return back()
                ->with('topAlert.title', 'ダウンロードできるファイルはありません');
        } catch (ZipArchiveNotSupportedException $e) {
            return back()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', 'このサーバーは、ZIPダウンロードに対応していません');
        }
    }
}
