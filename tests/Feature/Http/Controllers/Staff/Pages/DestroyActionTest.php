<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Tag;
use App\Eloquents\Page;
use App\Eloquents\Permission;
use App\Eloquents\Read;

class DestroyActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $staff;

    /** @var Tag */
    private $tag;

    /** @var Page */
    private $page;

    /** @var Read */
    private $read;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();
        $this->tag = factory(Tag::class)->create();
        $this->page = factory(Page::class)->create();
        $this->read = factory(Read::class, 5)->create(['page_id' => $this->page->id]);
    }

    /**
     * @test
     */
    public function お知らせを削除できる()
    {
        Permission::create(['name' => 'staff.pages.delete']);
        $this->staff->syncPermissions(['staff.pages.delete']);

        $this->page->viewableTags()->attach($this->tag->id);

        $this->assertDatabaseHas('pages', [
            'id' => $this->page->id,
        ]);

        $this->assertDatabaseHas('tags', [
            'id' => $this->tag->id,
        ]);

        $this->assertDatabaseHas('page_viewable_tags', [
            'page_id' => $this->page->id,
            'tag_id' => $this->tag->id,
        ]);

        $this->assertDatabaseHas('reads', [
            'page_id' => $this->page->id,
        ]);

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(
                route('staff.pages.destroy', [
                    'page' => $this->page->id,
                ])
            );

        $this->assertDatabaseMissing('pages', [
            'id' => $this->page->id,
        ]);

        $this->assertDatabaseMissing('page_viewable_tags', [
            'page_id' => $this->page->id,
            'tag_id' => $this->tag->id,
        ]);

        $this->assertDatabaseMissing('reads', [
            'page_id' => $this->page->id,
        ]);
    }

    /**
     * @test
     */
    public function 権限がない場合はお知らせを削除できない()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(
                route('staff.pages.destroy', [
                    'page' => $this->page->id,
                ])
            )
            ->assertForbidden();
    }
}
