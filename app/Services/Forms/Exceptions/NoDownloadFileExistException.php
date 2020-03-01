<?php

declare(strict_types=1);

namespace App\Services\Forms\Exceptions;

use Exception;

/**
 * ZIPダウンロード機能において、ダウンロード対象となる
 * ZIPファイルがない場合にスローされる例外
 */
class NoDownloadFileExistException extends Exception
{
}
