<?php

declare(strict_types=1);

namespace App\GridMakers;

interface GridMakable
{
    /**
     * フィルタやソート機能を利用した結果の配列を返す
     *
     * @param string $order_by ソート対象の列
     * @param string $direction ascかdesc
     * @param array $filter_queries フィルタークエリ
     * @param string $filter_mode フィルターのモード。 and か or
     * @param int $offset SQL で言う所の offset
     * @param int $limit SQL で言う所の limit
     * @return array
     */
    public function getArray(
        string $order_by,
        string $direction,
        array $filter_queries,
        string $filter_mode,
        int $offset,
        int $limit
    ): array;

    /**
     * フィルタ適用後の全件数を取得
     *
     * @param array $filter_queries フィルタークエリ
     * @param string $filter_mode フィルターのモード。 and か or
     * @return int
     */
    public function getCount(
        array $filter_queries,
        string $filter_mode
    ): int;

    /**
     * 表示するキー
     *
     * @return array
     */
    public function keys(): array;

    /**
     * フィルタ可能なキー
     *
     * [
     *      'keyName' => [
     *          'type' => 'タイプ(後述)',
     *          // belongsTo の場合
     *          'to' => 'belongsTo先テーブル名',
     *          'keys' => [
     *              'belongsTo先テーブルのkey名' => [
     *                  'translation' => '表示名',
     *                  'type' => 'タイプ(後述)'
     *              ],
     *              ...
     *          ]
     *          // belongsToMany の場合
     *          'pivot' => 'belongsToManyに利用している中間テーブル名',
     *          'foreign_key' => 'pivotテーブルにおける、自分側を表すidのカラム名',
     *          'related_key' => 'pivotテーブルにおける、リレーション先を表すidのカラム名',
     *          'choices' => [
     *              // 選択肢として表示する内容。choices配列の各要素はidを含む連想配列である必要がある
     *          ],
     *          'choices_name' => 'ドロップダウン内に表示される選択肢とするカラム名。 name や title など'
     *      ],
     *      ...
     * ]
     * という形式の連想配列を返してください。
     *
     * ※ タイプ : string, number, datetime, bool, isNull のいずれか
     * - string : 文字列検索
     * - number : 数値。大小比較ができる
     * - datetime : 日時。過去・未来比較ができる
     * - bool : ブーリアン。はい・いいえが選べる
     * - isNull : 空か空でないかで検索できる
     * - belongsTo : 他のテーブルに紐づく
     * - belongsToMany : belongsTo の複数バージョン
     *
     * @return array ['keyName' => 'type', ...] という形式の連想配列
     */
    public function filterableKeys(): array;

    /**
     * ソート可能なキー
     *
     * @return array
     */
    public function sortableKeys(): array;
}
