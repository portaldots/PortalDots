<?php

declare(strict_types=1);

namespace App\Services\Emails;

use RuntimeException;

/**
 * 一時的な送信制限により、メールを失敗扱いにせず次回へ持ち越す。
 */
class MailDeliveryDeferred extends RuntimeException
{
}
