<?php

declare(strict_types=1);

namespace App\GridMakers\Filter;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;

class FilterQueries implements IteratorAggregate
{
    /**
     * @var FilterQueryItem[]
     */
    private $queries = [];

    public function __construct(array $queries)
    {
        foreach ($queries as $query) {
            if (!$query instanceof FilterQueryItem) {
                throw new InvalidArgumentException('引数 $queries は' . FilterQueryItem::class . 'オブジェクトの配列である必要があります。');
            }
        }

        $this->queries = $queries;
    }

    public static function fromArray(array $queries)
    {
        return new self(
            array_reduce($queries, function (array $carry, $query) {
                try {
                    $carry[] = new FilterQueryItem($query['key_name'], $query['operator'], $query['value']);
                } catch (InvalidArgumentException $e) {
                    // バリデーションに引っかかるクエリは無視する
                }

                return $carry;
            }, [])
        );
    }

    public static function fromJson(string $json)
    {
        return self::fromArray(json_decode($json, true));
    }

    /**
     * @return ArrayIterator|FilterQueryItem[]
     */
    public function getIterator()
    {
        return new ArrayIterator($this->queries);
    }
}
