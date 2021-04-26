<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Circle;
use App\Eloquents\Place;
use App\Exports\PlacesExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

use function PHPSTORM_META\map;

class PlacesExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PlacesExport
     */
    private $placesExport;

    /**
     * @var Place
     */
    private $place;

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var Circle
     */
    private $anotherCircle;

    public function setUp(): void
    {
        parent::setUp();

        $this->placesExport = App::make(PlacesExport::class);

        $this->place = factory(Place::class)->create([
            'name' => 'いっそー',
            'type' => 3,
        ]);
        $this->circle = factory(Circle::class)->create([
            'name' => '備品貸出隊',
            'name_yomi' => 'びひんかしだしたい',
            'group_name' => 'ガレージセール愛好会',
            'group_name_yomi' => 'がれーじせーるあいこうかい',
        ]);
        $this->anotherCircle = factory(Circle::class)->create([
            'name' => '備品取り返し隊',
            'name_yomi' => 'びひんとりかえしたい',
            'group_name' => '備品ほしい団体',
            'group_name_yomi' => 'びひんほしいだんたい',
        ]);

        $this->place->circles()->attach($this->circle->id);
        $this->place->circles()->attach($this->anotherCircle->id);
    }

    /**
     * @test
     */
    public function map_ブース情報のフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                [
                    $this->place->id,
                    'いっそー',
                    '特殊場所',
                    $this->place->notes,
                    $this->circle->id,
                    '備品貸出隊',
                    'びひんかしだしたい',
                    'ガレージセール愛好会',
                    'がれーじせーるあいこうかい',
                ],
                [
                    null,
                    null,
                    null,
                    null,
                    $this->anotherCircle->id,
                    '備品取り返し隊',
                    'びひんとりかえしたい',
                    '備品ほしい団体',
                    'びひんほしいだんたい',
                ]
            ],
            $this->placesExport->map($this->place->load('circles'))
        );
    }
}
