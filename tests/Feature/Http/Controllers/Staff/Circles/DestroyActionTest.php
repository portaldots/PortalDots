<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Place;
use App\Eloquents\Tag;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyActionTest extends TestCase
{
    use RefreshDatabase;

    private $staff;
    private $user;
    private $circle;
    private $place;
    private $form;
    private $answer;
    private $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();

        $this->place = factory(Place::class)->create();

        $this->form = factory(Form::class)->create();
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $this->circle->id,
        ]);

        $this->tag = factory(Tag::class)->create();

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
        $this->circle->places()->attach($this->place->id);
        $this->circle->tags()->attach($this->tag->id);
    }

    /**
     * @test
     */
    public function 企画を削除すると関連する情報も削除される()
    {
        $responce = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(route('staff.circles.destroy', ['circle' => $this->circle]));

        $responce->assertRedirect(route('staff.circles.index'));

        $this->assertDatabaseMissing('answers', ['form_id' => $this->form->id, 'circle_id' => $this->circle->id]);
        $this->assertDatabaseMissing('circle_user', ['circle_id' => $this->circle->id]);
        $this->assertDatabaseMissing('circle_tag', ['circle_id' => $this->circle->id]);
        $this->assertDatabaseMissing('booths', ['circle_id' => $this->circle->id]);
    }
}
