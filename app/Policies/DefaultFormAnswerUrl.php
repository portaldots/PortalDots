<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\FormAnswerUrl;

class DefaultFormAnswerUrl implements FormAnswerUrl
{
    public function for(int $circleId, int $formId, ?int $answerId): string
    {
        return $answerId === null
            ? route('forms.answers.create', ['form' => $formId])
            : route('forms.answers.edit', ['form' => $formId, 'answer' => $answerId]);
    }
}
