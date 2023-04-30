<?php

declare(strict_types=1);

namespace App\GridMakers\Filter;

use JsonSerializable;

class FilterableKeyBelongsToManyWithoutChoicesOptions implements JsonSerializable
{
    /**
     * @param string $to
     * @param string $pivot
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey 主キー
     * @param FilterableKeysDict $parentKeys
     */
    public function __construct(
        private string $to,
        private string $pivot,
        private string $foreignPivotKey,
        private string $relatedPivotKey,
        private string $parentKey,
        private FilterableKeysDict $parentKeys
    ) {
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getPivot(): string
    {
        return $this->pivot;
    }

    public function getForeignPivotKey(): string
    {
        return $this->foreignPivotKey;
    }

    public function getRelatedPivotKey(): string
    {
        return $this->relatedPivotKey;
    }

    public function getParentKey(): string
    {
        return $this->parentKey;
    }

    public function getParentKeys(): FilterableKeysDict
    {
        return $this->parentKeys;
    }

    public function jsonSerialize()
    {
        return [
            'to' => $this->to,
            'pivot' => $this->pivot,
            'foreign_key' => $this->foreignPivotKey,
            'related_key' => $this->relatedPivotKey,
            'parent_key' => $this->parentKey,
            'keys' => $this->parentKeys,
        ];
    }
}
