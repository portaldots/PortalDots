<?php

declare(strict_types=1);

namespace App\Services\Forms;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
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
     * @param array $uploaded_file_infos  [(フルパス), (ZIPファイル内でのファイル名)] という形式のタプル配列
     * @throws NoDownloadFileExistException
     * @throws ZipArchiveNotSupportedException
     * @return string
     */
    public function makeZip(Form $form, array $uploaded_file_infos): string
    {
        if (! file_exists(storage_path('app/answer_details_zip'))) {
            Storage::makeDirectory('answer_details_zip');
        }

        if (!is_array($uploaded_file_infos) || count($uploaded_file_infos) === 0) {
            throw new NoDownloadFileExistException();
        }

        $zip_filename = 'uploads_' . $form->id . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zip_path = storage_path("app/answer_details_zip/{$zip_filename}");

        if ($this->zip->open($zip_path, ZipArchive::CREATE) !== true) {
            throw new ZipArchiveNotSupportedException();
        }

        foreach ($uploaded_file_infos as $tuple) {
            [$fullpath, $localname] = $tuple;
            $this->zip->addFile($fullpath, $localname);
        }

        $this->zip->close();

        // return Storage::download("answer_details_zip/{$zip_filename}", $zip_filename);
        return $zip_path;
    }
}
