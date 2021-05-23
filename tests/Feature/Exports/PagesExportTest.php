<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Page;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Exports\PagesExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class PagesExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PagesExport
     */
    private $pagesExport;

    /**
     * @var User
     */
    private $staff;

    /**
     * @var Tag
     */
    private $tag;

    /**
     * @var Page
     */
    private $page;

    public function setUp(): void
    {
        parent::setUp();

        $this->pagesExport = App::make(PagesExport::class);
        $this->staff = factory(User::class)->states('staff')->create([
            'name' => '野田 一郎',
        ]);
        $this->tag = factory(Tag::class)->create([
            'name' => 'タグです',
        ]);
        $this->page = factory(Page::class)->create([
            'created_by' => $this->staff->id,
            'updated_by' => $this->staff->id,
        ]);
        $this->page->viewableTags()->attach($this->tag->id);
    }

    /**
     * @test
     */
    public function map_お知らせのフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                $this->page->id,
                $this->page->title,
                'タグです',
                $this->page->body,
                $this->page->is_pinned,
                $this->page->is_public,
                $this->page->notes,
                $this->page->created_at,
                "野田 一郎(ID:{$this->staff->id},{$this->staff->student_id})",
                $this->page->updated_at,
                "野田 一郎(ID:{$this->staff->id},{$this->staff->student_id})",
            ],
            $this->pagesExport->map($this->page)
        );
    }
}
