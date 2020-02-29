<?php

declare(strict_types=1);

namespace App\Services\Forms;

use ZipArchive;
use Storage;
use App\Eloquents\Form;
use App\Services\Forms\Exceptions\NoDownloadFileExistException;
use App\Services\Forms\Exceptions\ZipArchiveNotSupportedException;

class DownloadZipService
{
    /**
     * ZipArchive インスタンス
     *
     * @var ZipArchive
     */
    private $zip;

    public function __construct(ZipArchive $zip)
    {
        $this->zip = $zip;
    }

    /**
     * 指定されたアップロードファイルパス配列からZIPファイルを作成し、
     * 作成したZIPファイルのパスを返す
     *
     * @param Form $form
     * @param array $uploaded_file_paths
     * @return string
     */
    public function makeZip(Form $form, array $uploaded_file_paths): string
    {
        if (! file_exists(storage_path('app/answer_details_zip'))) {
            Storage::makeDirectory('answer_details_zip');
        }

        // [(フルパス), (ZIPファイル内でのファイル名)] という形式のタプルにする
        $tuples = array_map(function ($path) {
            if (empty($path)) {
                return null;
            } elseif (
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
            throw new NoDownloadFileExistException();
        }

        $zip_filename = 'uploads_' . $form->id . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zip_path = storage_path("app/answer_details_zip/{$zip_filename}");

        if ($this->zip->open($zip_path, ZipArchive::CREATE) !== true) {
            throw new ZipArchiveNotSupportedException();
        }

        foreach ($tuples as $tuple) {
            [$fullpath, $localname] = $tuple;
            $this->zip->addFile($fullpath, $localname);
        }

        $this->zip->close();

        // return Storage::download("answer_details_zip/{$zip_filename}", $zip_filename);
        return $zip_path;
    }
}
