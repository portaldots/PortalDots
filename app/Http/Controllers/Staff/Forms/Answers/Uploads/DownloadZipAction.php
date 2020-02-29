<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

use ZipArchive;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use Storage;

class DownloadZipAction extends Controller
{
    public function __invoke(Form $form)
    {
        $form->load('answers.details');
        $form->load(['questions' => function ($query) {
            $query->where('type', 'upload');
        }]);

        $upload_question_ids = $form->questions->pluck('id')->all();
        $flatten_details = $form->answers->pluck('details')->flatten();

        $uploaded_file_paths = [];

        foreach ($flatten_details as $detail) {
            if (in_array($detail->question_id, $upload_question_ids, true)) {
                $uploaded_file_paths[] = $detail->answer;
            }
        }

        // dd($uploaded_file_paths);
        return $this->makeZip($form->id, $uploaded_file_paths);
    }

    private function makeZip(int $form_id, array $uploaded_file_paths)
    {
        if (! file_exists(storage_path('app/answer_details_zip'))) {
            Storage::makeDirectory('answer_details_zip');
        }

        $tuples = array_map(function ($path) {
            if (
                strpos($path, 'answer_details/') === 0 &&
                file_exists($fullpath = Storage::path($path)) &&
                is_file($fullpath)
            ) {
                // Project v2 申請フォームからアップロードされたファイル
                //
                // TODO: 将来的に、ダウンロードされるファイル名に answer_details__ は含めないようにしたい
                // TODO: 別件だが、回答一覧画面でも answer_details/ というパスは表示しないようにしたい
                return [$fullpath, str_replace('answer_details/', 'answer_details__', $path)];
            } elseif (
                file_exists($fullpath = config('portal.codeigniter_upload_dir') . '/form_file/' . basename($path)) &&
                is_file($fullpath)
            ) {
                // CodeIgniter 申請フォームからアップロードされたファイル
                //
                // 将来的に、この elseif 節は廃止する
                return [$fullpath, basename($path)];
            }
            return null;
        }, $uploaded_file_paths);
        $tuples = array_filter($tuples);

        if (!is_array($tuples) || count($tuples) === 0) {
            return back()
                ->with('topAlert.title', 'ダウンロードできるファイルはありません');
        }

        $zip = new ZipArchive();
        $zip_filename = 'uploads_' . $form_id . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zip_path = storage_path("app/answer_details_zip/{$zip_filename}");

        if ($zip->open($zip_path, ZipArchive::CREATE) !== true) {
            return back()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', 'このサーバーは、ZIPダウンロードに対応していません');
        }

        foreach ($tuples as $tuple) {
            [$fullpath, $localname] = $tuple;
            $zip->addFile($fullpath, $localname);
        }

        $zip->close();

        return Storage::download("answer_details_zip/{$zip_filename}", $zip_filename);
    }
}
