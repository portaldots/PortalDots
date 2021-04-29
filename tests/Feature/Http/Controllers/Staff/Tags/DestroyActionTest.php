<?php

namespace Tests\Feature\Http\Controllers\Staff\Tags;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Tag;
use App\Eloquents\Page;
use App\Eloquents\Form;
use App\Eloquents\Permission;

class DestroyActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $staff;

    /** @var Circle */
    private $circle;

    /** @var Tag */
    private $tag;

    /** @var Page */
    private $page;

    /** @var Form */
    private $form;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();

        $this->circle = factory(Circle::class)->create();
        $this->tag = factory(Tag::class)->create();
        $this->page = factory(Page::class)->create();
        $this->form = factory(Form::class)->create();
    }

    /**
     * @test
     */
    public function tagテーブルから削除すると関連テーブルからも削除される()
    {
        Permission::create(['name' => 'staff.tags.delete']);
        $this->staff->syncPermissions(['staff.tags.delete']);

        $this->circle->tags()->attach($this->tag->id);
        $this->page->viewableTags()->attach($this->tag->id);
        $this->form->answerableTags()->attach($this->tag->id);

        $this->assertDatabaseHas('tags', [
            'id' => $this->tag->id,
        ]);

        $this->assertDatabaseHas('circle_tag', [
            'circle_id' => $this->circle->id,
            'tag_id' => $this->tag->id,
        ]);

        $this->assertDatabaseHas('page_viewable_tags', [
            'page_id' => $this->page->id,
            'tag_id' => $this->tag->id,
        ]);

        $this->assertDatabaseHas('form_answerable_tags', [
            'form_id' => $this->form->id,
            'tag_id' => $this->tag->id,
        ]);

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(
                route('staff.tags.destroy', [
                    'tag' => $this->tag->id,
                ])
            );

        $this->assertDatabaseMissing('tags', [
            'id' => $this->tag->id,
        ]);

        $this->assertDatabaseMissing('circle_tag', [
            'circle_id' => $this->circle->id,
            'tag_id' => $this->tag->id,
        ]);

        $this->assertDatabaseMissing('page_viewable_tags', [
            'page_id' => $this->page->id,
            'tag_id' => $this->tag->id,
        ]);

        $this->assertDatabaseMissing('form_answerable_tags', [
            'form_id' => $this->form->id,
            'tag_id' => $this->tag->id,
        ]);
    }

    /**
     * @test
     */
    public function 権限がない場合はタグを削除できない()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(
                route('staff.tags.destroy', [
                    'tag' => $this->tag->id,
                ])
            )
            ->assertForbidden();
    }
}
