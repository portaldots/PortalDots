<?php

declare(strict_types=1);

namespace App\GridMakers\Filter;

use JsonSerializable;

class FilterableKeyBelongsToOptions implements JsonSerializable
{
    /**
     * @var string
     */
    private $to;

    /**
     * @var FilterableKeysDict
     */
    private $keys;

    public function __construct(string $to, FilterableKeysDict $keys)
    {
        $this->to = $to;
        $this->keys = $keys;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getKeys(): FilterableKeysDict
    {
        return $this->keys;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'to' => $this->to,
            'keys' => $this->keys,
        ];
    }
}
