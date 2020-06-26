<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;

abstract class BaseGridMaker
{

    // TODO: いま GridResponder クラスにある composedQuery メソッドは、GridMaker で定義できるようにする
    // ↑ Laravel のクエリビルダを使わないデータソース(申請フォームの回答等)に対してソートやフィルターを使えるようにするため

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
     * type : string, number, datetime, bool, isNull のいずれか
     * - string : 文字列検索
     * - number : 数値。大小比較ができる
     * - datetime : 日時。過去・未来比較ができる
     * - bool : ブーリアン。はい・いいえが選べる
     * - isNull : 空か空でないかで検索できる
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
        foreach ($this->keys() as $key) {
            $item[$key] = $record->$key;
        }
        return $item;
    }
}
