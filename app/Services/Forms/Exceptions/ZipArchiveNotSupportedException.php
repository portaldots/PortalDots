<?php

declare(strict_types=1);

namespace App\Services\Forms\Exceptions;

use Exception;

/**
 * ZipArchive クラスが利用できない時にスローされる例外
 */
class ZipArchiveNotSupportedException extends Exception
{
}
