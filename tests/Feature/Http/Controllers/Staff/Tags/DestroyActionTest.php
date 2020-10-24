<?php

namespace Tests\Feature\Http\Controllers\Staff\Tags;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Tag;
use App\Eloquents\Page;
use App\Eloquents\Form;

class DestroyActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function tagテーブルから削除すると関連テーブルからも削除される()
    {
        $staff = factory(User::class)->states('staff')->create();

        $circle = factory(Circle::class)->create();
        $tag = factory(Tag::class)->create();
        $page = factory(Page::class)->create();
        $form = factory(Form::class)->create();

        $circle->tags()->attach($tag->id);
        $page->viewableTags()->attach($tag->id);
        $form->answerableTags()->attach($tag->id);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
        ]);

        $this->assertDatabaseHas('circle_tag', [
            'circle_id' => $circle->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('page_viewable_tags', [
            'page_id' => $page->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseHas('form_answerable_tags', [
            'form_id' => $form->id,
            'tag_id' => $tag->id,
        ]);

        $response = $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->delete(
                route('staff.tags.destroy', [
                    'tag' => $tag->id,
                ])
            );

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('circle_tag', [
            'circle_id' => $circle->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('page_viewable_tags', [
            'page_id' => $page->id,
            'tag_id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('form_answerable_tags', [
            'form_id' => $form->id,
            'tag_id' => $tag->id,
        ]);
    }
}
