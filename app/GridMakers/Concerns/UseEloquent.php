<?php

declare(strict_types=1);

namespace App\GridMakers\Concerns;

use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use App\GridMakers\Filter\FilterQueries;
use App\GridMakers\Filter\FilterQueryItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use InvalidArgumentException;

trait UseEloquent
{
    /**
     * 表示に利用する Eloquent Model
     *
     * @return Model
     */
    abstract protected function model(): Model;

    /**
     * 表示に利用する Eloquent のクエリビルダオブジェクト
     *
     * @return Builder
     */
    abstract protected function baseEloquentQuery(): Builder;

    /**
     * 1レコードの配列を生成して返す
     *
     * @param $record
     * @return array
     */
    abstract protected function map($record): array;

    /**
     * フィルタ可能なキー
     *
     * @return FilterableKeysDict
     */
    abstract public function filterableKeys(): FilterableKeysDict;

    /**
     * @inheritDoc
     */
    public function defaultOrderBy(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     */
    public function defaultDirection(): string
    {
        return 'asc';
    }

    /**
     * フィルタクエリ配列等をもとに、フィルタ適用済みのクエリビルダオブジェクトを生成する
     *
     * @param Builder $query
     * @param FilterQueries $filter_queries
     * @param string $filter_mode
     * @return Builder
     */
    protected function makeFilterAppliedQuery(Builder $query, FilterQueries $filter_queries, string $filter_mode)
    {
        if (!in_array($filter_mode, ['and', 'or'], true)) {
            throw new InvalidArgumentException('$filter_mode は and か or のどちらかで指定してください。');
        }

        return $query->where(function (Builder $db_query) use ($filter_queries, $filter_mode) {
            $filterable_keys = $this->filterableKeys();

            foreach ($filter_queries as $filter_query) {
                $key_name = $filter_query->getMainKeyName();
                $filterable_key = $filterable_keys->getByKey($key_name);

                if ($filterable_key->getType() === FilterableKey::TYPE_BELONGS_TO_MANY) {
                    $sub_query_function = function ($sub_query) use (
                        $filterable_key,
                        $filter_query
                    ) {
                        $options = $filterable_key->getBelongsToManyOptions();
                        $pivot = $options->getPivot();
                        $foreign_key = $options->getForeignKey();

                        $sub_query->from($pivot)
                            ->select("{$pivot}.{$foreign_key}")
                            ->where($options->getRelatedKey(), (int)$filter_query->getValue());
                    };

                    $db_query->whereIn(
                        $this->model()->getTable() . '.' . $this->model()->getKeyName(),
                        $sub_query_function,
                        $filter_mode
                    );
                } elseif ($filterable_key->getType() === FilterableKey::TYPE_BELONGS_TO) {
                    $sub_query_function = function ($sub_query) use (
                        $filterable_key,
                        $filter_query,
                        $filter_mode
                    ) {
                        $options = $filterable_key->getBelongsToOptions();
                        $belongs_to = $options->getTo();

                        $sub_query->from($belongs_to)
                            ->select("{$belongs_to}.{$this->model()->getKeyName()}");

                        $this->addToDbQuery(
                            $sub_query,
                            $options->getKeys()->getByKey($filter_query->getSubKeyName())->getType(),
                            new FilterQueryItem(
                                // ドット(.)より後ろの文字列のみ
                                $filter_query->getSubKeyName(),
                                $filter_query->getOperator(),
                                $filter_query->getValue()
                            ),
                            $filter_mode
                        );
                    };

                    $db_query->whereIn(
                        $this->model()->getTable() . '.' . $key_name,
                        $sub_query_function,
                        $filter_mode
                    );
                } else {
                    $this->addToDbQuery(
                        $db_query,
                        $filterable_key->getType(),
                        $filter_query,
                        $filter_mode
                    );
                }
            }
        });
    }

    private function addToDbQuery(&$db_query, $type, FilterQueryItem $filter_query, $filter_mode)
    {
        $query_key_name_for_sql = $filter_query->getFullKeyName();
        $query_value_for_sql = $filter_query->getValue();

        if (in_array($filter_query->getOperator(), ['like', 'not like'], true)) {
            $query_value_for_sql = '%' . $filter_query->getValue() . '%';
        }

        switch ($type) {
            case FilterableKey::TYPE_STRING:
                if (!in_array($filter_query->getOperator(), ['=', '!=', 'like', 'not like'], true)) {
                    return;
                }
                break;
            case FilterableKey::TYPE_NUMBER:
                if (!in_array($filter_query->getOperator(), ['=', '!=', '<', '>', '<=', '>='], true)) {
                    return;
                }
                $query_value_for_sql = (double)$filter_query->getValue();
                break;
            case FilterableKey::TYPE_DATETIME:
                if (!in_array($filter_query->getOperator(), ['=', '!=', '<', '>', '<=', '>='], true)) {
                    return;
                }
                $query_key_name_for_sql = "date_format(`{$filter_query->getFullKeyName()}`, '%Y-%m-%d %H:%i')";
                $query_value_for_sql = (new Carbon($filter_query->getValue()))->format('Y-m-d H:i');
                break;
            case FilterableKey::TYPE_BOOL:
                if (!in_array($filter_query->getOperator(), ['='], true)) {
                    return;
                }
                $query_value_for_sql = (int)$filter_query->getValue() === 0 ? 0 : 1;
                break;
            case FilterableKey::TYPE_IS_NULL:
                $query_value_for_sql = (int)$filter_query->getValue() === 0 ? false : true;
                break;
            case FilterableKey::TYPE_ENUM:
                $choices = $this->filterableKeys()->getByKey($filter_query->getFullKeyName())->getEnumChoices();

                if (
                    !in_array(
                        $filter_query->getValue(),
                        $choices,
                        true
                    )
                ) {
                    return;
                }

                if ($filter_query->getValue() === 'NULL') {
                    $type = FilterableKey::TYPE_IS_NULL;
                    $query_value_for_sql = true;
                }
                break;
        }

        if ($type === FilterableKey::TYPE_IS_NULL) {
            $is = $query_value_for_sql ? 'NULL' : 'NOT NULL';
            $db_query->whereRaw("{$query_key_name_for_sql} IS {$is}", null, $filter_mode);
        } elseif ($filter_query->getOperator() === '!=') {
            $db_query->whereRaw("NOT ({$query_key_name_for_sql} <=> ?)", [$query_value_for_sql], $filter_mode);
        } elseif ($type === FilterableKey::TYPE_DATETIME) {
            $db_query->whereRaw(
                "{$query_key_name_for_sql} {$filter_query->getOperator()} ?",
                [$query_value_for_sql],
                $filter_mode
            );
        } else {
            $db_query->where(
                $query_key_name_for_sql,
                $filter_query->getOperator(),
                $query_value_for_sql,
                $filter_mode
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getArray(
        string $order_by,
        string $direction,
        FilterQueries $filter_queries,
        string $filter_mode,
        int $offset,
        int $limit
    ): array {
        if (!in_array($order_by, $this->sortableKeys(), true)) {
            $direction = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = $this->baseEloquentQuery()->orderBy($order_by, $direction);

        $query = $this->makeFilterAppliedQuery($query, $filter_queries, $filter_mode);

        return $query->offset($offset)->limit($limit)->get()->map(function ($record) {
            return $this->map($record);
        })->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getCount(
        FilterQueries $filter_queries,
        string $filter_mode
    ): int {
        return $this->makeFilterAppliedQuery($this->baseEloquentQuery(), $filter_queries, $filter_mode)->count();
    }
}
