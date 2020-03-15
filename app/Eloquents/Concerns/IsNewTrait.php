<?php

declare(strict_types=1);

namespace App\Eloquents\Concerns;

trait IsNewTrait
{
    public function isNew()
    {
        if (empty($this->created_at)) {
            throw new \Exception('created_at プロパティがありません');
        }

        $added_time = $this->created_at->add('3days');
        return $added_time->gte(now());
    }
}
