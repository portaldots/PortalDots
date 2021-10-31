<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Services\Forms\DownloadZipService;
use App\Services\Forms\Exceptions\NoDownloadFileExistException;
use App\Services\Forms\Exceptions\ZipArchiveNotSupportedException;
use Illuminate\Support\Facades\Storage;

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
            $query->where('type', 'upload');
        }]);

        $upload_question_ids = $form->questions->pluck('id')->all();
        $flatten_details = $form->answers->filter(function ($answer) {
            return !empty($answer->circle->submitted_at);
        })->pluck('details')->flatten();

        // [(フルパス), (ZIPファイル内でのファイル名)] という形式のタプルの配列にする
        $uploaded_file_infos = $flatten_details->map(function ($detail) use ($upload_question_ids) {
            if (!in_array($detail->question_id, $upload_question_ids, true)) {
                return null;
            }

            $path = $detail->answer;
            $user_friendly_file_name = $detail->user_friendly_file_name;

            dd($path, $detail->toArray());

            if (empty($path) || empty($user_friendly_file_name)) {
                return null;
            } elseif (
                strpos($path, 'answer_details/') === 0 &&
                file_exists($fullpath = Storage::path($path)) &&
                is_file($fullpath)
            ) {
                return [$fullpath, $user_friendly_file_name];
            }
            return null;
        })
        ->filter()
        ->toArray();

        try {
            $zip_path = $this->downloadZipService->makeZip($form, $uploaded_file_infos);
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
