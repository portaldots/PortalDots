<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Circle;
use App\Eloquents\Tag;
use App\Exports\TagsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class TagsExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var TagsExport
     */
    private $tagsExport;

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var Circle
     */
    private $anotherCircle;

    /**
     * @var Tag
     */
    private $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tagsExport = App::make(TagsExport::class);
        $this->circle = factory(Circle::class)->create([
            'name' => 'タグがついた企画',
            'name_yomi' => 'たぐがついたきかく',
            'group_name' => '商品タグ愛好会',
            'group_name_yomi' => 'しょうひんたぐあいこうかい',
        ]);
        $this->anotherCircle = factory(Circle::class)->create([
            'name' => 'タグをつけられた企画',
            'name_yomi' => 'たぐをつけられたきかく',
            'group_name' => '企画タグつけてほしい団体',
            'group_name_yomi' => 'きかくたぐつけてほしいだんたい',
        ]);
        $this->tag = factory(Tag::class)->create([
            'name' => 'ぼくタグです',
        ]);

        $this->tag->circles()->attach([
            $this->circle->id,
            $this->anotherCircle->id,
        ]);
    }

    /**
     * @test
     */
    public function map_タグ情報のフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                [
                    $this->tag->id,
                    'ぼくタグです',
                    $this->tag->created_at,
                    $this->tag->updated_at,
                    $this->circle->id,
                    'タグがついた企画',
                    'たぐがついたきかく',
                    '商品タグ愛好会',
                    'しょうひんたぐあいこうかい',
                ],
                [
                    null,
                    null,
                    null,
                    null,
                    $this->anotherCircle->id,
                    'タグをつけられた企画',
                    'たぐをつけられたきかく',
                    '企画タグつけてほしい団体',
                    'きかくたぐつけてほしいだんたい',
                ]
            ],
            $this->tagsExport->map($this->tag->load('circles'))
        );
    }
}
