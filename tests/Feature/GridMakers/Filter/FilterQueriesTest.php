<?php

namespace Tests\Feature\GridMakers\Filter;

use App\GridMakers\Filter\FilterQueries;
use App\GridMakers\Filter\FilterQueryItem;
use InvalidArgumentException;
use Tests\TestCase;

class FilterQueriesTest extends TestCase
{
    /**
     * @test
     */
    public function constructor_正常()
    {
        $queries = [
            new FilterQueryItem('id', '=', '3'),
            new FilterQueryItem('name', 'not like', 'PortalDots'),
        ];

        $obj = new FilterQueries($queries);

        $this->assertInstanceOf(FilterQueries::class, $obj);
    }

    /**
     * @test
     */
    public function constructor_引数配列に違うオブジェクトが入っていたら例外が発生する()
    {
        $this->expectException(InvalidArgumentException::class);

        $queries = [
            new FilterQueryItem('id', '=', '3'),
            ['key_name' => 'title', 'operator' => '!=', 'value' => 'PortalDots'],
            new FilterQueryItem('name', 'not like', 'PortalDots'),
        ];

        new FilterQueries($queries);
    }

    /**
     * @test
     */
    public function fromArray()
    {
        $obj = FilterQueries::fromArray([
            ['key_name' => 'id', 'operator' => '=', 'value' => '3'],
            ['key_name' => 'title', 'operator' => '!=', 'value' => 'PortalDots'],
            // 不正なオペレータ
            ['key_name' => 'body', 'operator' => '<>', 'value' => 'これは本文です。'],
            // 必要なパラメータがセットされていない
            ['key_name' => 'notes', 'value' => 'オペレータ未指定'],
            ['operator' => '=', 'value' => 'キーネーム未指定'],
            // valueが空なのは許容
            ['key_name' => 'name', 'operator' => 'not like', 'value' => ''],
            ['key_name' => 'created_at', 'operator' => '>'],
        ]);

        $this->assertInstanceOf(FilterQueries::class, $obj);

        // fromArray で指定した項目のうち、不正な項目が除外されていることを確認
        $expected = [
            ['key_name' => 'id', 'operator' => '=', 'value' => '3'],
            ['key_name' => 'title', 'operator' => '!=', 'value' => 'PortalDots'],
            ['key_name' => 'name', 'operator' => 'not like', 'value' => ''],
            ['key_name' => 'created_at', 'operator' => '>', 'value' => ''],
        ];
        $actual = array_map(function (FilterQueryItem $item) {
            return [
                'key_name' => $item->getFullKeyName(),
                'operator' => $item->getOperator(),
                'value' => $item->getValue(),
            ];
        }, iterator_to_array($obj->getIterator()));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function fromJson()
    {
        $obj = FilterQueries::fromJson(json_encode([
            ['key_name' => 'id', 'operator' => '=', 'value' => '3'],
            ['key_name' => 'title', 'operator' => '!=', 'value' => 'PortalDots'],
            // 不正なオペレータ
            ['key_name' => 'body', 'operator' => '<>', 'value' => 'これは本文です。'],
            // 必要なパラメータがセットされていない
            ['key_name' => 'notes', 'value' => 'オペレータ未指定'],
            ['operator' => '=', 'value' => 'キーネーム未指定'],
            // valueが空なのは許容
            ['key_name' => 'name', 'operator' => 'not like', 'value' => ''],
            ['key_name' => 'created_at', 'operator' => '>'],
        ]));

        $this->assertInstanceOf(FilterQueries::class, $obj);

        // fromArray で指定した項目のうち、不正な項目が除外されていることを確認
        $expected = [
            ['key_name' => 'id', 'operator' => '=', 'value' => '3'],
            ['key_name' => 'title', 'operator' => '!=', 'value' => 'PortalDots'],
            ['key_name' => 'name', 'operator' => 'not like', 'value' => ''],
            ['key_name' => 'created_at', 'operator' => '>', 'value' => ''],
        ];
        $actual = array_map(function (FilterQueryItem $item) {
            return [
                'key_name' => $item->getFullKeyName(),
                'operator' => $item->getOperator(),
                'value' => $item->getValue(),
            ];
        }, iterator_to_array($obj->getIterator()));

        $this->assertEquals($expected, $actual);
    }
}
