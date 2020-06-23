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
     * @param array $filters (TODO: 未実装)
     * @return Builder
     */
    private function composedQuery(string $order_by, string $direction, array $filters): Builder
    {
        if (!in_array($order_by, $this->gridMaker->sortableKeys(), true)) {
            $direction = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = $this->gridMaker->query()->orderBy($order_by, $direction);

        // TODO: フィルタ機能の実装

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
        $filters = [];

        $collection = $this->composedQuery($order_by, $direction, $filters)->offset(($page - 1) * $per_page)
                ->limit($per_page)->get();
        $paginator = new LengthAwarePaginator(
            $collection,
            $this->composedQuery($order_by, $direction, $filters)->count(),
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
