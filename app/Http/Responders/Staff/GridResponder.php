<?php

declare(strict_types=1);

namespace App\Http\Responders\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Responders\Respondable;
use App\Http\Responders\Staff\Exceptions\GridMakerNotSetException;
use App\Http\Responders\Staff\Exceptions\RequestNotSetException;
use App\GridMakers\BaseGridMaker;

/**
 * スタッフモード内の全レコード一覧ページを表示するためのレスポンダ
 */
class GridResponder implements Respondable
{
    /**
     * @var Request|null
     */
    private $request = null;

    /**
     * @var BaseGridMaker|null
     */
    private $gridMaker = null;

    /**
     * リクエストオブジェクトをセットする
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * グリッドを表示するために使用する GridMaker オブジェクトをセットする
     *
     * @param BaseGridMaker $gridMaker
     * @return $this
     */
    public function setGridMaker(BaseGridMaker $gridMaker)
    {
        $this->gridMaker = $gridMaker;
        return $this;
    }

    /**
     * フィルタやソート機能を利用した結果のクエリビルダオブジェクトを返す
     *
     * @param string $order_by ソート対象の列
     * @param string $order ascかdesc
     * @param array $filter_queries フィルタークエリ
     * @param string $filter_mode フィルターのモード。and か or
     * @return Builder
     */
    private function composedQuery(
        string $order_by,
        string $direction,
        array $filter_queries,
        string $filter_mode
    ): Builder {
        if (!in_array($order_by, $this->gridMaker->sortableKeys(), true)) {
            $direction = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = $this->gridMaker->query()->orderBy($order_by, $direction);

        // フィルタ機能
        $query->where(function ($db_query) use ($filter_queries, $filter_mode) {
            foreach ($filter_queries as $filter_query) {
                if (
                    !in_array(
                        $this->gridMaker->filterableKeys()[$filter_query['key_name']],
                        ['string', 'number', 'datetime', 'bool', 'isNull'],
                        true
                    )
                ) {
                    continue;
                }

                if (empty($this->gridMaker->filterableKeys()[$filter_query['key_name']])) {
                    continue;
                }

                if (in_array($filter_query['operator'], ['like', 'not like'], true)) {
                    $filter_query['value'] = '%' . $filter_query['value'] . '%';
                }

                switch ($this->gridMaker->filterableKeys()[$filter_query['key_name']]) {
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

                if ($this->gridMaker->filterableKeys()[$filter_query['key_name']] === 'isNull') {
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

    public function response(): Response
    {
        if (empty($this->request) || !($this->request instanceof Request)) {
            throw new RequestNotSetException('setRequestメソッドでRequestオブジェクトをセットしてからresponseメソッドを呼び出してください');
        }

        if (empty($this->gridMaker) || !($this->gridMaker instanceof BaseGridMaker)) {
            throw new GridMakerNotSetException('setGridMakerメソッドでGridMakerオブジェクトをセットしてからresponseメソッドを呼び出してください');
        }

        $page = !empty($this->request->page) ? (int)$this->request->page : 1;
        $per_page = !empty($this->request->per_page) ? (int)$this->request->per_page : 25;
        $order_by = !empty($this->request->order_by) ? $this->request->order_by : 'id';
        $direction = !empty($this->request->direction) ? $this->request->direction : 'asc';
        $filter_queries = !empty($this->request->queries) ? json_decode($this->request->queries, true) : [];
        $filter_mode = !empty($this->request->mode) ? $this->request->mode : 'and';

        $collection = $this->composedQuery(
            $order_by,
            $direction,
            $filter_queries,
            $filter_mode
        )->offset(($page - 1) * $per_page)
                ->limit($per_page)->get();
        $paginator = new LengthAwarePaginator(
            $collection,
            $this->composedQuery($order_by, $direction, $filter_queries, $filter_mode)->count(),
            $per_page,
            $page,
            [
                'path' => '/' . $this->request->path(),
                'query' => [
                    'per_page' => $per_page
                ]
            ]
        );
        return response([
            'keys' => $this->gridMaker->keys(),
            'paginator' => $paginator,
            'sortable_keys' => $this->gridMaker->sortableKeys(),
            'filterable_keys' => $this->gridMaker->filterableKeys(),
            'order_by' => $order_by,
            'direction' => $direction,
        ]);
    }
}
