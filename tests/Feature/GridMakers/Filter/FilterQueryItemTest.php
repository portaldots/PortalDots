<?php

namespace Tests\Feature\GridMakers\Filter;

use App\GridMakers\Filter\FilterQueryItem;
use InvalidArgumentException;
use Tests\TestCase;

class FilterQueryItemTest extends TestCase
{
    public static function operatorsProvider()
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("operatorsProvider")]
    public function constructor_正常(string $operator, string $expectedOperator)
    {
        $obj = new FilterQueryItem('this_is_key.sub', $operator, 'hogehoge');

        $this->assertInstanceOf(FilterQueryItem::class, $obj);
        $this->assertSame($expectedOperator, $obj->getOperator());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructor_必要な引数が空の場合は例外が発生する()
    {
        $this->expectException(InvalidArgumentException::class);

        new FilterQueryItem('', '', 'hogehoge');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructor_存在しない演算子が指定されたら例外が発生する()
    {
        $this->expectException(InvalidArgumentException::class);

        new FilterQueryItem('this_is_key.sub', '<>', 'hogehoge');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getFullKeyName()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('this_is_key.sub', $obj->getFullKeyName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getMainKeyName()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('this_is_key', $obj->getMainKeyName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getSubKeyName()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('sub', $obj->getSubKeyName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("operatorsProvider")]
    public function getOperator(string $input, string $output)
    {
        $obj = new FilterQueryItem('this_is_key.sub', $input, 'hogehoge');

        $this->assertEquals($output, $obj->getOperator());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getValue()
    {
        $obj = new FilterQueryItem('this_is_key.sub', '=', 'hogehoge');

        $this->assertEquals('hogehoge', $obj->getValue());
    }
}
