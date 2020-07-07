<?php

declare(strict_types=1);

namespace App\Http\Responders\Staff\Exceptions;

use Exception;

/**
 * Request オブジェクトをセットしないで GridResponder がレスポンス
 * を返そうとしたときに発生する例外
 */
class RequestNotSetException extends Exception
{
}
