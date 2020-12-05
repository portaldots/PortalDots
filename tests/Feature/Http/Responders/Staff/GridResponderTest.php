<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses


namespace Tests\Feature\Http\Responders\Staff;

use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use App\GridMakers\Filter\FilterQueries;
use App\GridMakers\GridMakable;
use App\Http\Responders\Staff\Exceptions\GridMakerNotSetException;
use App\Http\Responders\Staff\Exceptions\RequestNotSetException;
use App\Http\Responders\Staff\GridResponder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class GridResponderTest extends TestCase
{
    /**
     * @test
     */
    public function response_setRequestされていない場合は例外発生()
    {
        $this->expectException(RequestNotSetException::class);

        $obj = new GridResponder();
        $obj->setGridMaker(new MockedGridMaker());

        $obj->response();
    }

    /**
     * @test
     */
    public function response()
    {
        $request = new Request([
            'page' => 2,
            'per_page' => 22,
            'order_by' => 'name',
            'direction' => 'asc',
            'queries' => json_encode([
                ['key_name' => 'created_at', 'operator' => '<', 'value' => '2020/12/31 11:11:11'],
                ['key_name' => 'updated_at', 'operator' => '<', 'value' => '2021/12/31 11:11:11'],
            ]),
            'mode' => 'or'
        ]);

        $obj = new GridResponder();

        // モック
        $grid_maker = Mockery::mock(MockedGridMaker::class);
        $filter_queries_argument = Mockery::on(function (FilterQueries $arg) {
            $array = iterator_to_array($arg->getIterator());
            return $array[0]->getFullKeyName() === 'created_at'
                && $array[0]->getOperator() === '<'
                && $array[0]->getValue() === '2020/12/31 11:11:11'
                && $array[1]->getFullKeyName() === 'updated_at'
                && $array[1]->getOperator() === '<'
                && $array[1]->getValue() === '2021/12/31 11:11:11';
        });
        $grid_maker->shouldReceive('keys')
            ->once()
            ->andReturn([
                'id',
                'name',
                'created_at',
                'updated_at',
            ]);
        $grid_maker->shouldReceive('filterableKeys')
            ->once()
            ->andReturn(new FilterableKeysDict([
                'id' => FilterableKey::number(),
                'name' => FilterableKey::string(),
                'created_at' => FilterableKey::datetime(),
                'updated_at' => FilterableKey::datetime(),
            ]));
        $grid_maker->shouldReceive('sortableKeys')
            ->once()
            ->andReturn([
                'id',
                'name',
            ]);
        $grid_maker->shouldReceive('getArray')
            ->once()
            ->with(
                'name',
                'asc',
                $filter_queries_argument,
                'or',
                22,
                22
            );
        $grid_maker->shouldReceive('getCount')
            ->once()
            ->with(
                $filter_queries_argument,
                'or'
            )
            ->andReturn(78);


        /** @var MockedGridMaker $grid_maker */
        $obj->setGridMaker($grid_maker);
        $obj->setRequest($request);

        // レスポンス検証
        $response = $obj->response();

        $this->assertInstanceOf(Response::class, $response);

        $expected = json_encode([
            'keys' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
            'paginator' => [
                'current_page' => 2,
                'data' => [],
                'first_page_url' => '?per_page=22&page=1',
                'from' => null,
                'last_page' => 4,
                'last_page_url' => '?per_page=22&page=4',
                'next_page_url' => '?per_page=22&page=3',
                'path' => '',
                'per_page' => 22,
                'prev_page_url' => '?per_page=22&page=1',
                'to' => null,
                'total' => 78,
            ],
            'sortable_keys' => [
                'id',
                'name',
            ],
            'filterable_keys' => [
                'id' => ['type' => 'number'],
                'name' => ['type' => 'string'],
                'created_at' => ['type' => 'datetime'],
                'updated_at' => ['type' => 'datetime'],
            ],
            'order_by' => 'name',
            'direction' => 'asc'
        ]);

        $this->assertJsonStringEqualsJsonString($expected, $response->content());
    }
}

class MockedGridMaker implements GridMakable
{
    public function getArray(
        string $order_by,
        string $direction,
        FilterQueries $filter_queries,
        string $filter_mode,
        int $offset,
        int $limit
    ): array {
        return [];
    }

    public function getCount(FilterQueries $filter_queries, string $filter_mode): int
    {
        return 0;
    }

    public function keys(): array
    {
        return [];
    }

    public function filterableKeys(): FilterableKeysDict
    {
        return new FilterableKeysDict([]);
    }

    public function sortableKeys(): array
    {
        return [];
    }
}
