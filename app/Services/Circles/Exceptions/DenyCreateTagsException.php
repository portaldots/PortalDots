<?php

declare(strict_types=1);

namespace App\Services\Circles\Exceptions;

use Exception;

/**
 * 企画の保存の際、新しくタグを作成するのを許可されていないのに
 * 企画タグを作成しようとしたときに発生する例外
 */
class DenyCreateTagsException extends Exception
{
}
