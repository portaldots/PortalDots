<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Circle;
use App\Eloquents\Place;
use App\Exports\PlacesExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

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
        ]);
        $this->anotherCircle = factory(Circle::class)->create([
            'name' => '備品取り返し隊',
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
                $this->place->id,
                'いっそー',
                '特殊場所',
                $this->place->notes,
                "[{\"id\":{$this->circle->id},\"name\":\"備品貸出隊\"},"
                . "{\"id\":{$this->anotherCircle->id},\"name\":\"備品取り返し隊\"}]"
            ],
            $this->placesExport->map($this->place->load('circles:id,name'))
        );
    }
}
