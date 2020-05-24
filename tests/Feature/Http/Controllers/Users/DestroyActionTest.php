<?php

namespace Tests\Feature\Http\Controllers\Users;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Circle;

class DestroyActionTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $circle;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     */
    public function アカウント削除ができる()
    {
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('user.destroy'));

        $this->assertDatabaseMissing('users', [
            'id' => $this->user->id,
        ]);

        $response->assertRedirect(route('home'));
    }

    /**
     * @test
     */
    public function 管理者ユーザーは削除できない()
    {
        $this->user->is_admin = true;
        $this->user->save();

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'is_admin' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('user.destroy'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'is_admin' => 1,
        ]);
    }

    /**
     * @test
     */
    public function 企画に所属しているユーザーは削除できない()
    {
        $circle = factory(Circle::class)->create();
        $this->user->circles()->attach($circle->id);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('user.destroy'));

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
        ]);
    }
}
