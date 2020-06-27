<?php

declare(strict_types=1);

namespace App\GridMakers\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait UseEloquent
{
    /**
     * 表示に利用する Eloquent のクエリビルダオブジェクト
     *
     * @return Builder
     */
    abstract public function baseEloquentQuery(): Builder;

    /**
     * @inheritDoc
     */
    public function query(
        string $order_by,
        string $direction,
        array $filter_queries,
        string $filter_mode
    ): Builder {
        if (!in_array($order_by, $this->sortableKeys(), true)) {
            $direction = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = $this->baseEloquentQuery()->orderBy($order_by, $direction);

        // フィルタ機能
        $query->where(function ($db_query) use ($filter_queries, $filter_mode) {
            foreach ($filter_queries as $filter_query) {
                if (
                    !in_array(
                        $this->filterableKeys()[$filter_query['key_name']],
                        ['string', 'number', 'datetime', 'bool', 'isNull'],
                        true
                    )
                ) {
                    continue;
                }

                if (empty($this->filterableKeys()[$filter_query['key_name']])) {
                    continue;
                }

                if (in_array($filter_query['operator'], ['like', 'not like'], true)) {
                    $filter_query['value'] = '%' . $filter_query['value'] . '%';
                }

                switch ($this->filterableKeys()[$filter_query['key_name']]) {
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

                if ($this->filterableKeys()[$filter_query['key_name']] === 'isNull') {
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
        });

        return $query;
    }

    /**
     * @inheritDoc
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
