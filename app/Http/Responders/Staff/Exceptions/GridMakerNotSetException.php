<?php

declare(strict_types=1);

namespace App\Http\Responders\Staff\Exceptions;

use Exception;

/**
 * GridMaker オブジェクトをセットしないで GridResponder がレスポンス
 * を返そうとしたときに発生する例外
 */
class GridMakerNotSetException extends Exception
{
}
