<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Http\Controllers\Circles\BaseTestCase;
use App\Eloquents\User;
use App\Eloquents\Circle;

class DoneActionTest extends BaseTestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;

    public function setUp(): void
    {
        parent::setUp();

        $this->participationForm->confirmation_message = "これが確認メッセージです。";
        $this->participationForm->save();

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create([
            'participation_type_id' => $this->participationType->id
        ]);

        $this->user->circles()->attach($this->circle->id, ['is_leader' => true]);
    }

    /**
     * @test
     */
    public function 参加登録の提出後に表示する内容が表示される()
    {
        $response = $this
            ->actingAs($this->user)
            ->withSession(['done' => true])
            ->get(
                route('circles.done', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertSee('これが確認メッセージです。');
    }

    /**
     * @test
     */
    public function セッションがセットされていない場合はアクセスできない()
    {
        $response = $this
            ->actingAs($this->user)
            ->get(
                route('circles.done', [
                    'circle' => $this->circle,
                ])
            );

        $response->assertRedirect(route('home'));
    }
}
