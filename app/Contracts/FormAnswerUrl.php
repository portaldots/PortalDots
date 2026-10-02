<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * 申請の送付・差し戻しメールで使う回答先 URL。
 */
interface FormAnswerUrl
{
    public function for(int $circleId, int $formId, ?int $answerId): string;
}
