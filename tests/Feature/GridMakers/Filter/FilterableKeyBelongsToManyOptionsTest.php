<?php

namespace Tests\Feature\GridMakers\Filter;

use App\GridMakers\Filter\FilterableKeyBelongsToManyOptions;
use Tests\TestCase;

class FilterableKeyBelongsToManyOptionsTest extends TestCase
{
    public function instantiate()
    {
        return new FilterableKeyBelongsToManyOptions(
            'circle_user',
            'circle_id',
            'user_id',
            [
                'id' => 1, 'name' => 'Aさん',
                'id' => 2, 'name' => 'Bさん',
                'id' => 3, 'name' => 'Cさん',
            ],
            'name'
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructor()
    {
        $obj = $this->instantiate();
        $this->assertInstanceOf(FilterableKeyBelongsToManyOptions::class, $obj);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getPivot()
    {
        $obj = $this->instantiate();
        $this->assertEquals('circle_user', $obj->getPivot());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getForeignKey()
    {
        $obj = $this->instantiate();
        $this->assertEquals('circle_id', $obj->getForeignKey());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getRelatedKey()
    {
        $obj = $this->instantiate();
        $this->assertEquals('user_id', $obj->getRelatedKey());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getChoices()
    {
        $obj = $this->instantiate();
        $this->assertEquals([
                'id' => 1, 'name' => 'Aさん',
                'id' => 2, 'name' => 'Bさん',
                'id' => 3, 'name' => 'Cさん',
            ], $obj->getChoices());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getChoicesName()
    {
        $obj = $this->instantiate();
        $this->assertEquals('name', $obj->getChoicesName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function jsonSerialize()
    {
        $obj = $this->instantiate();
        $expected = json_encode([
            'pivot' => 'circle_user',
            'foreign_key' => 'circle_id',
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
