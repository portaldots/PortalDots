<?php

namespace Tests\Feature\GridMakers\Filter;

use App\GridMakers\Filter\FilterableKeyBelongsToManyOptions;
use Tests\TestCase;

class FilterableKeyBelongsToManyOptionsTest extends TestCase
{
    public function instantiate()
    {
        return new FilterableKeyBelongsToManyOptions(
            'group_user',
            'group_id',
            'user_id',
            [
                'id' => 1, 'name' => 'Aさん',
                'id' => 2, 'name' => 'Bさん',
                'id' => 3, 'name' => 'Cさん',
            ],
            'name'
        );
    }

    /**
     * @test
     */
    public function constructor()
    {
        $obj = $this->instantiate();
        $this->assertInstanceOf(FilterableKeyBelongsToManyOptions::class, $obj);
    }

    /**
     * @test
     */
    public function getPivot()
    {
        $obj = $this->instantiate();
        $this->assertEquals('group_user', $obj->getPivot());
    }

    /**
     * @test
     */
    public function getForeignKey()
    {
        $obj = $this->instantiate();
        $this->assertEquals('group_id', $obj->getForeignKey());
    }

    /**
     * @test
     */
    public function getRelatedKey()
    {
        $obj = $this->instantiate();
        $this->assertEquals('user_id', $obj->getRelatedKey());
    }

    /**
     * @test
     */
    public function getChoices()
    {
        $obj = $this->instantiate();
        $this->assertEquals([
            'id' => 1, 'name' => 'Aさん',
            'id' => 2, 'name' => 'Bさん',
            'id' => 3, 'name' => 'Cさん',
        ], $obj->getChoices());
    }

    /**
     * @test
     */
    public function getChoicesName()
    {
        $obj = $this->instantiate();
        $this->assertEquals('name', $obj->getChoicesName());
    }

    /**
     * @test
     */
    public function jsonSerialize()
    {
        $obj = $this->instantiate();
        $expected = json_encode([
            'pivot' => 'group_user',
            'foreign_key' => 'group_id',
            'related_key' => 'user_id',
            'choices' => [
                'id' => 1, 'name' => 'Aさん',
                'id' => 2, 'name' => 'Bさん',
                'id' => 3, 'name' => 'Cさん',
            ],
            'choices_name' => 'name'
        ]);

        $this->assertJsonStringEqualsJsonString($expected, json_encode($obj));
    }
}
