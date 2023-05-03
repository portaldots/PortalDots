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
        $i = 0;

        foreach ($dict as $key => $value) {
            if ($key === $i++) {
                throw new InvalidArgumentException('引数は連想配列である必要があります。');
            }

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

    public function jsonSerialize(): mixed
    {
        return $this->dict;
    }
}
