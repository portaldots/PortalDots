<?php

declare(strict_types=1);

namespace App\Services\Forms;

use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    private UploadedFilesService $uploadedFilesService;

    public function __construct(ZipArchive $zip, UploadedFilesService $uploadedFilesService)
    {
        $this->zip = $zip;
        $this->uploadedFilesService = $uploadedFilesService;
    }

    /**
     * 指定されたアップロードファイルパス配列からZIPファイルを作成し、
     * 作成したZIPファイルのパスを返す
     *
     * @param Form $form
     * @param array $uploaded_file_paths
     * @throws NoDownloadFileExistException
     * @throws ZipArchiveNotSupportedException
     * @return string
     */
    public function makeZip(Form $form, array $uploaded_file_paths): string
    {
        // [(フルパス), (ZIPファイル内でのファイル名)] という形式のタプルにする
        $tuples = [];
        foreach ($uploaded_file_paths as $path) {
            $fullpath = $this->uploadedFilesService->getPath($path);
            if ($fullpath !== null) {
                $tuples[] = [$fullpath, basename($path)];
            }
        }

        if (count($tuples) === 0) {
            throw new NoDownloadFileExistException();
        }

        Storage::makeDirectory('answer_details_zip');
        $zip_filename = 'uploads_' . $form->id . '_' . now()->format('Y-m-d_H-i-s') . '_' . Str::uuid() . '.zip';
        $relative_path = "answer_details_zip/{$zip_filename}";
        $zip_path = Storage::path($relative_path);

        if ($this->zip->open($zip_path, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            throw new ZipArchiveNotSupportedException();
        }

        $complete = false;
        try {
            foreach ($tuples as [$fullpath, $localname]) {
                if (!$this->zip->addFile($fullpath, $localname)) {
                    throw new ZipArchiveNotSupportedException();
                }
            }
            $complete = true;
        } finally {
            $closed = false;
            try {
                $closed = $this->zip->close();
            } finally {
                if (!$complete || !$closed) {
                    Storage::delete($relative_path);
                }
            }
        }

        if (!$closed) {
            throw new ZipArchiveNotSupportedException();
        }

        return $zip_path;
    }
}
