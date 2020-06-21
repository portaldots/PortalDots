<?php

declare(strict_types=1);

namespace App\Http\Responders\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function response(): Response
    {
        if (empty($this->request) || !($this->request instanceof Request)) {
            throw new RequestNotSetException('setRequestメソッドでRequestオブジェクトをセットしてからresponseメソッドを呼び出してください');
        }

        if (empty($this->gridMaker) || !($this->gridMaker instanceof BaseGridMaker)) {
            throw new GridMakerNotSetException('setGridMakerメソッドでGridMakerオブジェクトをセットしてからresponseメソッドを呼び出してください');
        }

        if ($this->request->is_ajax) {
            $page = !empty($this->request->page) ? (int)$this->request->page : 1;
            $per_page = !empty($this->request->per_page) ? (int)$this->request->per_page : 20;
            $collection = $this->gridMaker->query()->offset(($page - 1) * $per_page)
                    ->limit($per_page)->get();
            $paginator = new LengthAwarePaginator(
                $collection,
                $this->gridMaker->query()->count(),
                $per_page,
                $page,
                [
                    'path' => '/' . $this->request->path() . "?is_ajax=true&per_page={$per_page}"
                ]
            );
            return response([
                'keys' => $this->gridMaker->keys(),
                'paginator' => $paginator,
            ]);
        }

        return response(view($this->gridMaker->view()));
    }
}
