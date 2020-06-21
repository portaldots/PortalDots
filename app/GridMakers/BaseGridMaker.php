<?php

declare(strict_types=1);

namespace App\GridMakers;

abstract class BaseGridMaker
{
    /**
     * 表示用に利用するクエリビルダオブジェクト
     *
     * @return mixed
     */
    abstract public function query();

    /**
     * 表示するキー
     *
     * @return array
     */
    abstract public function keys(): array;

    /**
     * フィルタ可能なキー
     *
     * @return array
     */
    abstract public function filterableKeys(): array;

    /**
     * ソート可能なキー
     *
     * @return array
     */
    abstract public function sortableKeys(): array;

    /**
     * 1レコードの配列を生成して返す
     *
     * @return array
     */
    public function map($record): array
    {
        $item = [];
        foreach ($this->keys as $key) {
            $item[] = $record[$key];
        }
        return $item;
    }
}
