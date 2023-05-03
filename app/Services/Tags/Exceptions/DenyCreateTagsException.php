<?php

declare(strict_types=1);

namespace App\Services\Tags\Exceptions;

use Exception;

/**
 * 新しくタグを作成するのを許可されていないのに
 * 企画タグを作成しようとしたときに発生する例外
 */
class DenyCreateTagsException extends Exception
{
}
