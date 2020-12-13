<?php

namespace Tests\Feature\GridMakers\Filter;

use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeyBelongsToManyOptions;
use App\GridMakers\Filter\FilterableKeyBelongsToOptions;
use App\GridMakers\Filter\FilterableKeysDict;
use BadMethodCallException;
use Tests\TestCase;

class FilterableKeyTest extends TestCase
{
    public function typesWithNoOptionsProvider()
    {
        return [
            ['string'],
            ['number'],
            ['datetime'],
            ['bool'],
            ['isNull'],
        ];
    }

    /**
     * @test
     * @dataProvider typesWithNoOptionsProvider
     */
    public function オプションなしでインスタンス化できる(string $type)
    {
        $obj = FilterableKey::$type();

        $this->assertEquals($type, $obj->getType());
    }

    /**
     * @test
     * @dataProvider typesWithNoOptionsProvider
     */
    public function jsonSerialize_オプションなしtypeのオブジェクトをシリアライズできる(string $type)
    {
        $expected = json_encode(['type' => $type]);
        $actual = json_encode(FilterableKey::$type());
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @test
     * @dataProvider typesWithNoOptionsProvider
     */
    public function getBelongsToOptions_オプションなしtypeの場合は例外発生する(string $type)
    {
        $this->expectException(BadMethodCallException::class);
        FilterableKey::$type()->getBelongsToOptions();
    }

    /**
     * @test
     * @dataProvider typesWithNoOptionsProvider
     */
    public function getBelongsToManyOptions_オプションなしtypeの場合は例外発生する(string $type)
    {
        $this->expectException(BadMethodCallException::class);
        FilterableKey::$type()->getBelongsToManyOptions();
    }

    /**
     * @test
     * @dataProvider typesWithNoOptionsProvider
     */
    public function getEnumChoices_オプションなしtypeの場合は例外発生する(string $type)
    {
        $this->expectException(BadMethodCallException::class);
        FilterableKey::$type()->getEnumChoices();
    }

    /**
     * @test
     */
    public function belongsTo_引数を渡せばインスタンス化できる()
    {
        $obj = FilterableKey::belongsTo('this_is_related_table_name', new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]));

        $this->assertEquals('belongsTo', $obj->getType());
        $this->assertEquals('this_is_related_table_name', $obj->getBelongsToOptions()->getTo());

        $this->assertInstanceOf(FilterableKeyBelongsToOptions::class, $obj->getBelongsToOptions());

        $this->assertEquals('number', $obj->getBelongsToOptions()->getKeys()->getByKey('id')->getType());
        $this->assertEquals('string', $obj->getBelongsToOptions()->getKeys()->getByKey('name')->getType());
        $this->assertEquals('datetime', $obj->getBelongsToOptions()->getKeys()->getByKey('created_at')->getType());
        $this->assertEquals('datetime', $obj->getBelongsToOptions()->getKeys()->getByKey('updated_at')->getType());
    }

    /**
     * @test
     */
    public function jsonSerialize_typeがbelongsToのオブジェクトをシリアライズできる()
    {
        $expected = json_encode([
            'type' => 'belongsTo',
            'to' => 'this_is_related_table_name',
            'keys' => [
                'id' => [
                    'type' => 'number',
                ],
                'name' => [
                    'type' => 'string',
                ],
                'created_at' => [
                    'type' => 'datetime',
                ],
                'updated_at' => [
                    'type' => 'datetime',
                ],
            ]
        ]);

        $obj = FilterableKey::belongsTo('this_is_related_table_name', new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]));

        $this->assertJsonStringEqualsJsonString($expected, json_encode($obj));
    }

    /**
     * @test
     */
    public function belongsToMany_引数を渡せばインスタンス化できる()
    {
        // class_student は架空のテーブル名
        $obj = FilterableKey::belongsToMany('class_student', 'class_id', 'student_id', [
            ['id' => 1, 'name' => 'テスト太郎'],
            ['id' => 2, 'name' => 'テスト花子'],
        ], 'name');

        $this->assertInstanceOf(FilterableKeyBelongsToManyOptions::class, $obj->getBelongsToManyOptions());
    }

    /**
     * @test
     */
    public function jsonSerialize_typeがbelongsToManyのオブジェクトをシリアライズできる()
    {
        $expected = json_encode([
            'type' => 'belongsToMany',
            'pivot' => 'class_student',
            'foreign_key' => 'class_id',
            'related_key' => 'student_id',
            'choices' => [
                ['id' => 1, 'name' => 'テスト太郎'],
                ['id' => 2, 'name' => 'テスト花子'],
            ],
            'choices_name' => 'name',
        ]);

        // class_student は架空のテーブル名
        $obj = FilterableKey::belongsToMany('class_student', 'class_id', 'student_id', [
            ['id' => 1, 'name' => 'テスト太郎'],
            ['id' => 2, 'name' => 'テスト花子'],
        ], 'name');

        $this->assertJsonStringEqualsJsonString($expected, json_encode($obj));
    }

    /**
     * @test
     */
    public function enum_引数を渡せばインスタンス化できる()
    {
        $obj = FilterableKey::enum(['rejected', 'approved', 'NULL']);

        $this->assertEquals('enum', $obj->getType());
        $this->assertEquals(['rejected', 'approved', 'NULL'], $obj->getEnumChoices());
    }

    /**
     * @test
     */
    public function jsonSerialize_typeがenumのオブジェクトをシリアライズできる()
    {
        $expected = json_encode([
            'type' => 'enum',
            'choices' => [
                'rejected',
                'approved',
                'NULL',
            ]
        ]);

        $obj = FilterableKey::enum(['rejected', 'approved', 'NULL']);

        $this->assertJsonStringEqualsJsonString($expected, json_encode($obj));
    }
}
