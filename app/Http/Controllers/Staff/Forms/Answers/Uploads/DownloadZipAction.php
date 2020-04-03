<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Services\Forms\DownloadZipService;
use App\Services\Forms\Exceptions\NoDownloadFileExistException;
use App\Services\Forms\Exceptions\ZipArchiveNotSupportedException;

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

    public function __invoke(int $form_id)
    {
        $form = Form::withoutGlobalScope('withoutCustomForms')->findOrFail($form_id);

        $form->load('answers.details');
        $form->load(['answers.circle' => function ($query) {
            $query->withoutGlobalScope('approved');
        }]);
        $form->load(['questions' => function ($query) {
            $query->where('type', 'upload');
        }]);

        $upload_question_ids = $form->questions->pluck('id')->all();
        $flatten_details = $form->answers->filter(function ($answer) {
            return !empty($answer->circle->submitted_at);
        })->pluck('details')->flatten();

        $uploaded_file_paths = [];

        foreach ($flatten_details as $detail) {
            if (in_array($detail->question_id, $upload_question_ids, true)) {
                $uploaded_file_paths[] = $detail->answer;
            }
        }

        try {
            $zip_path = $this->downloadZipService->makeZip($form, $uploaded_file_paths);
            return response()->download($zip_path);
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
