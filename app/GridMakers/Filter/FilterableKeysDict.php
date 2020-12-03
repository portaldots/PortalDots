<?php

declare(strict_types=1);

namespace App\GridMakers\Filter;

use JsonSerializable;
use InvalidArgumentException;
use Exception;

class FilterableKeysDict implements JsonSerializable
{
    private $dict;

    public function __construct(array $dict)
    {
        foreach ($dict as $key => $value) {
            if (!$value instanceof FilterableKey) {
                throw new InvalidArgumentException(FilterableKey::class . 'オブジェクト配列を引数として渡してください。');
            }
        }

        $this->dict = $dict;
    }

    public function getByKey(string $key): FilterableKey
    {
        if (empty($this->dict[$key])) {
            throw new Exception("{$key} というキーは FilterableKeysDict には存在しません。");
        }

        return $this->dict[$key];
    }

    public function jsonSerialize()
    {
        return $this->dict;
    }
}
