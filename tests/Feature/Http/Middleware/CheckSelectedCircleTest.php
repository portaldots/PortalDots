<?php

namespace Tests\Feature\Http\Middleware;

use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;
use App\Http\Middleware\CheckSelectedCircle;

class CheckSelectedCircleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->circle = factory(Circle::class)->create();
        $this->user = factory(User::class)->create();

        $this->circle->users()->attach($this->user->id, ['is_leader' => true]);
    }

    /**
     * @test
     */
    public function 未ログイン状態の場合はミドルウェアがスキップされる()
    {
        $this->mock(SelectorService::class, function ($mock) {
            $mock->shouldNotReceive('setCircle');
        });

        $has_test_passed = false;

        App::make(CheckSelectedCircle::class)->handle(App::make('request'), function () use (&$has_test_passed) {
            $has_test_passed = true;
        });

        $this->assertTrue($has_test_passed);
    }

    /**
     * @test
     */
    public function 一つの企画にしか所属していない場合はその企画に自動ログインされる()
    {
        $this->mock(SelectorService::class, function ($mock) {
            $mock->shouldReceive('reset')->andReturn(null);
            $mock->shouldReceive('getCircle')->andReturn(null);
            $mock->shouldReceive('setCircle')->once()->with(\Mockery::on(function ($arg) {
                return $arg->id === $this->circle->id && $arg->name === $this->circle->name;
            }))->andReturn(null);
        });

        $this->be($this->user);

        $has_test_passed = false;

        App::make(CheckSelectedCircle::class)->handle(App::make('request'), function () use (&$has_test_passed) {
            $has_test_passed = true;
        });

        $this->assertTrue($has_test_passed);
    }

    /**
     * @test
     */
    public function 二つの企画に所属している場合は企画セレクターへリダイレクトされる()
    {
        $this->mock(SelectorService::class, function ($mock) {
            $mock->shouldReceive('reset')->andReturn(null);
            $mock->shouldReceive('getCircle')->andReturn(null);
        });

        $this->user->circles()->attach(factory(Circle::class)->create(), ['is_leader' => true]);

        $this->be($this->user);

        $original_response = App::make(CheckSelectedCircle::class)->handle(App::make('request'), function () {
        });

        $response = $this->createTestResponse($original_response);

        $response->assertRedirect(route('circles.selector.show', ['redirect_to' => '/']));
    }
}
