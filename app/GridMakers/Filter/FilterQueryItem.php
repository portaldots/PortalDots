<?php

declare(strict_types=1);

namespace App\GridMakers\Filter;

use InvalidArgumentException;

class FilterQueryItem
{
    /**
     * フィルタ対象のキー名
     *
     * ただし、belongsTo と belongsToMany については、(key_name).(belongsTo先のkey_name) という形式
     *
     * @var string
     */
    private $fullKeyName;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var mixed
     */
    private $value;

    public function __construct(string $fullKeyName, string $operator, $value)
    {
        if (empty($fullKeyName) || empty($operator)) {
            throw new InvalidArgumentException('キーまたは演算子が指定されていません。');
        }

        $operator = strtolower($operator);


        if (!in_array($operator, ['=', '!=', '<', '>', '<=', '>=', 'like', 'not like'], true)) {
            throw new InvalidArgumentException('利用できない $operator が指定されました。');
        }

        $this->fullKeyName = $fullKeyName;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getFullKeyName(): string
    {
        return $this->fullKeyName;
    }

    public function getMainKeyName(): string
    {
        return explode('.', $this->fullKeyName)[0];
    }

    public function getSubKeyName(): ?string
    {
        return explode('.', $this->fullKeyName)[1] ?? null;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue()
    {
        return $this->value;
    }
}
