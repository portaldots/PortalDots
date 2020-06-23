<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseGridMaker
{
    /**
     * 表示用に利用するクエリビルダオブジェクト
     *
     * @return Builder
     */
    abstract public function query(): Builder;

    /**
     * 表示するキー
     *
     * @return array
     */
    abstract public function keys(): array;

    /**
     * フィルタ可能なキー
     *
     * ['keyName' => 'type', ...] という形式の連想配列を返してください。
     *
     * type : string, int, datetime, bool のいずれか
     *
     * @return array ['keyName' => 'type', ...] という形式の連想配列
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
