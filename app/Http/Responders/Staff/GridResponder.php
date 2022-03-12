<?php

declare(strict_types=1);

namespace App\Http\Responders\Staff;

use App\GridMakers\Filter\FilterQueries;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Responders\Respondable;
use App\Http\Responders\Staff\Exceptions\GridMakerNotSetException;
use App\Http\Responders\Staff\Exceptions\RequestNotSetException;
use App\GridMakers\GridMakable;

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
     * @var GridMakable|null
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
     * @param GridMakable $gridMaker
     * @return $this
     */
    public function setGridMaker(GridMakable $gridMaker)
    {
        $this->gridMaker = $gridMaker;
        return $this;
    }

    public function response(): Response
    {
        if (empty($this->request) || !($this->request instanceof Request)) {
            throw new RequestNotSetException('setRequestメソッドでRequestオブジェクトをセットしてからresponseメソッドを呼び出してください');
        }

        if (empty($this->gridMaker) || !($this->gridMaker instanceof GridMakable)) {
            throw new GridMakerNotSetException('setGridMakerメソッドでGridMakerオブジェクトをセットしてからresponseメソッドを呼び出してください');
        }

        $page = !empty($this->request->page) ? (int)$this->request->page : 1;
        $per_page = !empty($this->request->per_page) ? (int)$this->request->per_page : 25;
        $order_by = !empty($this->request->order_by)
            ? $this->request->order_by
            : $this->gridMaker->defaultOrderBy();
        $direction = !empty($this->request->direction)
            ? $this->request->direction
            : $this->gridMaker->defaultDirection();
        $filter_queries = !empty($this->request->queries)
            ? FilterQueries::fromJson($this->request->queries)
            : FilterQueries::fromArray(([]));
        $filter_mode = !empty($this->request->mode) && in_array($this->request->mode, ['and', 'or'], true)
            ? $this->request->mode
            : 'and';

        $paginator = new LengthAwarePaginator(
            $this->gridMaker->getArray(
                $order_by,
                $direction,
                $filter_queries,
                $filter_mode,
                ($page - 1) * $per_page,
                $per_page
            ),
            $this->gridMaker->getCount($filter_queries, $filter_mode),
            $per_page,
            $page,
            [
                'path' => '/' . $this->request->path(),
                'query' => [
                    'per_page' => $per_page
                ]
            ]
        );
        unset($paginator['links']);
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
