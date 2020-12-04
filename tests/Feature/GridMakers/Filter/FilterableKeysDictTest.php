<?php

namespace Tests\Feature\GridMakers\Filter;

use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Exception;
use InvalidArgumentException;
use Tests\TestCase;

class FilterableKeysDictTest extends TestCase
{
    /**
     * @test
     */
    public function constructor_空配列でもインスタンス化できる()
    {
        $obj = new FilterableKeysDict([]);

        $this->assertInstanceOf(FilterableKeysDict::class, $obj);
    }

    /**
     * @test
     */
    public function jsonSerialize_空配列の場合()
    {
        $obj = new FilterableKeysDict([]);

        $this->assertJsonStringEqualsJsonString(json_encode([]), json_encode($obj));
    }

    /**
     * @test
     */
    public function constructor_配列の内部に違う型のオブジェクトが入っている場合は例外発生する()
    {
        $this->expectException(InvalidArgumentException::class);

        new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'created_at' => ['type' => 'datetime']
        ]);
    }

    /**
     * @test
     */
    public function constructor_引数が連想配列ではない場合例外発生する()
    {
        $this->expectException(InvalidArgumentException::class);

        new FilterableKeysDict([
            FilterableKey::number(),
            FilterableKey::string(),
        ]);
    }

    /**
     * @test
     */
    public function constructor_正常()
    {
        $obj = new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'status' => FilterableKey::enum(['rejected', 'approved', 'NULL']),
        ]);

        $this->assertInstanceOf(FilterableKeysDict::class, $obj);
    }

    /**
     * @test
     */
    public function getByKey_正常()
    {
        $obj = new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'status' => FilterableKey::enum(['rejected', 'approved', 'NULL']),
        ]);

        $this->assertEquals('number', $obj->getByKey('id')->getType());
        $this->assertEquals('string', $obj->getByKey('name')->getType());
        $this->assertEquals('enum', $obj->getByKey('status')->getType());
    }

    /**
     * @test
     */
    public function getByKey_存在しないキーが指定された場合は例外発生する()
    {
        $this->expectException(Exception::class);

        $obj = new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'status' => FilterableKey::enum(['rejected', 'approved', 'NULL']),
        ]);

        $obj->getByKey('foobar');
    }

    /**
     * @test
     */
    public function jsonSerialize()
    {
        $expected = json_encode([
            'id' => ['type' => 'number'],
            'name' => ['type' => 'string'],
            'status' => [
                'type' => 'enum',
                'choices' => [
                    'rejected',
                    'approved',
                    'NULL',
                ]
            ]
        ]);

        $obj = new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'status' => FilterableKey::enum(['rejected', 'approved', 'NULL']),
        ]);

        $this->assertJsonStringEqualsJsonString($expected, json_encode($obj));
    }
}
