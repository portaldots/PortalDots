<?php

declare(strict_types=1);

namespace App\GridMakers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
     * フィルタクエリ配列等をもとに、フィルタ適用済みのクエリビルダオブジェクトを生成する
     *
     * @param Builder $query
     * @param array $filter_queries
     * @param string $filter_mode
     * @return Builder
     */
    protected function makeFilterAppliedQuery(Builder $query, array $filter_queries, string $filter_mode)
    {
        return $query->where(function ($db_query) use ($filter_queries, $filter_mode) {
            foreach ($filter_queries as $filter_query) {
                // belongsTo と belongsToMany については、key_name が (key_name).(belongsTo先のkey_name) という形式で
                // 送られてくるので、ピリオドで分割して key_name のみを取り出す
                $key_name = explode('.', $filter_query['key_name'])[0];

                if (
                    !empty($this->filterableKeys()[$key_name]) &&
                    !in_array(
                        $this->filterableKeys()[$key_name]['type'],
                        ['string', 'number', 'datetime', 'bool', 'isNull', 'belongsTo'],
                        true
                    )
                ) {
                    // TODO: belongsToMany にも対応する
                    continue;
                }

                if (empty($this->filterableKeys()[$key_name]['type'])) {
                    continue;
                }

                if ($this->filterableKeys()[$key_name]['type'] === 'belongsTo') {
                    $sub_query_function = function ($sub_query) use ($key_name, $filter_query, $filter_mode) {
                        $belongs_to = $this->filterableKeys()[$key_name]['to'];

                        $sub_query->from($belongs_to)
                            ->select("{$belongs_to}.{$this->model()->getKeyName()}");
                        $filter_query['key_name'] = explode('.', $filter_query['key_name'])[1];

                        if (empty($this->filterableKeys()[$key_name]['keys'][$filter_query['key_name']])) {
                            // 許可されていない key_name を SQL に渡してしまうと、SQLインジェクションが発生する恐れがあるため、
                            // ここで処理を中止する
                            return;
                        }

                        $this->addToDbQuery(
                            $sub_query,
                            $this->filterableKeys()[$key_name]['keys'][$filter_query['key_name']]['type'],
                            $filter_query,
                            $filter_mode
                        );
                    };
                    if ($filter_mode === 'and') {
                        $db_query->whereIn(
                            $this->model()->getTable() . '.' . $key_name,
                            $sub_query_function
                        );
                    } else {
                        $db_query->orWhereIn(
                            $this->model()->getTable() . '.' . $key_name,
                            $sub_query_function
                        );
                    }
                } else {
                    $this->addToDbQuery(
                        $db_query,
                        $this->filterableKeys()[$key_name]['type'],
                        $filter_query,
                        $filter_mode
                    );
                }
            }
        });
    }

    private function addToDbQuery(&$db_query, $type, $filter_query, $filter_mode)
    {
        if (in_array($filter_query['operator'], ['like', 'not like'], true)) {
            $filter_query['value'] = '%' . $filter_query['value'] . '%';
        }

        switch ($type) {
            case 'string':
                if (!in_array($filter_query['operator'], ['=', '!=', 'like', 'not like'], true)) {
                    $filter_query['operator'] = '=';
                }
                break;
            case 'number':
                if (!in_array($filter_query['operator'], ['=', '!=', '<', '>', '<=', '>='], true)) {
                    $filter_query['operator'] = '=';
                }
                $filter_query['value'] = (double)$filter_query['value'];
                break;
            case 'datetime':
                if (!in_array($filter_query['operator'], ['=', '!=', '<', '>', '<=', '>='], true)) {
                    $filter_query['operator'] = '=';
                }
                break;
            case 'bool':
                if (!in_array($filter_query['operator'], ['='], true)) {
                    $filter_query['operator'] = '=';
                }
                $filter_query['value'] = (int)$filter_query['value'] === 0 ? 0 : 1;
                break;
            case 'isNull':
                $filter_query['value'] = (int)$filter_query['value'] === 0 ? false : true;
                break;
        }

        if ($type === 'isNull') {
            $is = $filter_query['value'] ? 'NULL' : 'NOT NULL';
            if ($filter_mode === 'and') {
                $db_query->whereRaw("{$filter_query['key_name']} IS {$is}");
            } else {
                $db_query->orWhereRaw("{$filter_query['key_name']} IS {$is}");
            }
        } elseif ($filter_query['operator'] === '!=') {
            if ($filter_mode === 'and') {
                $db_query->whereRaw("NOT ({$filter_query['key_name']} <=> ?)", [$filter_query['value']]);
            } else {
                $db_query->orWhereRaw("NOT ({$filter_query['key_name']} <=> ?)", [$filter_query['value']]);
            }
        } elseif ($filter_mode === 'and') {
            $db_query->where($filter_query['key_name'], $filter_query['operator'], $filter_query['value']);
        } else {
            $db_query->orWhere($filter_query['key_name'], $filter_query['operator'], $filter_query['value']);
        }
    }

    /**
     * @inheritDoc
     */
    public function getArray(
        string $order_by,
        string $direction,
        array $filter_queries,
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
        array $filter_queries,
        string $filter_mode
    ): int {
        return $this->makeFilterAppliedQuery($this->baseEloquentQuery(), $filter_queries, $filter_mode)->count();
    }
}
