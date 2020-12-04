<?php

namespace Tests\Feature\GridMakers\Filter;

use App\GridMakers\Filter\FilterQueryItem;
use InvalidArgumentException;
use Tests\TestCase;

class FilterQueryItemTest extends TestCase
{
    public function operatorsProvider()
    {
        return [
            ['=', '='],
            ['!=', '!='],
            ['<', '<'],
            ['>', '>'],
            ['<=', '<='],
            ['>=', '>='],
            ['like', 'like'],
            ['not like', 'not like'],
            ['LIKE', 'like'],
            ['NOT LIKE', 'not like'],
            ['LiKe', 'like'],
            ['NoT lIkE', 'not like'],
        ];
    }

    /**
     * @test
     * @dataProvider operatorsProvider
     */
    public function constructor_正常(string $operator)
    {
        $obj = new FilterQueryItem('this_is_key.sub', $operator, 'hogehoge');

        $this->assertInstanceOf(FilterQueryItem::class, $obj);
    }

    /**
     * @test
     */
    public function constructor_存在しない演算子が指定されたら例外が発生する()
    {
        $this->expectException(InvalidArgumentException::class);

        new FilterQueryItem('this_is_key.sub', '<>', 'hogehoge');
    }

    /**
     * @test
     */
    public function getFullKeyName()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('this_is_key.sub', $obj->getFullKeyName());
    }

    /**
     * @test
     */
    public function getMainKeyName()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('this_is_key', $obj->getMainKeyName());
    }

    /**
     * @test
     */
    public function getSubKeyName()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('sub', $obj->getSubKeyName());
    }

    /**
     * @test
     * @dataProvider operatorsProvider
     */
    public function getOperator(string $input, string $output)
    {
        $obj = new FilterQueryItem('this_is_key.sub', $input, 'hogehoge');

        $this->assertEquals($output, $obj->getOperator());
    }

    /**
     * @test
     */
    public function getValue()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('hogehoge', $obj->getValue());
    }
}
